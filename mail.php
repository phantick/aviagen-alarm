<?php
include_once("___auth.data.php");

require_once("vendor/Logger.php");
require_once("vendor/Server.php");
require_once("vendor/Message.php");
require_once("vendor/MIME.php");
require_once("vendor/MysqliDb.php");
require_once("vendor/dbObject.php");
include_once("vendor/smsc_api.php");

use Katzgrau\KLogger\Logger;

use Fetch\Server;
use Fetch\Message;
use Fetch\MIME;

function isValidSender($sender) {
    return ($sender->host == "cornet.ooo" || ($sender->mailbox == "bigdutchmanrus" && $sender->host == "gmail.com")) ? true : false;
}

function isValidSubject($subject) {
    return substr($subject, 0, 7) === "!!SMS!!" ? true : false;
}

function sortMailboxUsers($a, $b) {
    if ($a->ORDERNUMBER < $b->ORDERNUMBER) return -1;
    else if ($a->ORDERNUMBER > $b->ORDERNUMBER) return 1;
    else return 0;
}

function sendSMS($phone, $text) {
	global $log;

	$log->info("Send SMS.", array($phone, $text));
	$ret = (object)array();
	$rc = send_sms($phone, $text);
	if (Count($rc) == 2) {
		$log->error("Can't send SMS.", $rc);
		$ret->sms_uid = $rc[0];
		$ret->error = +$rc[1];
	} else if (Count($rc) == 4) {
		$log->info("SMS has been sent.");
		$ret->sms_uid = $rc[0];
	} else {
	    $log->error("Wrong answer from SMS provider: ", $rc);
	    $ret->error = 999;
	}
	return $ret;
}

function getSMSStatus($uid, $phone) {
	global $log;

	$log->info("Get SMS status. UID=".$uid);
	$ret = (object)array();
	$rc = get_status($uid, $phone);
    $log->info("SMS status: ", $rc);
	if (Count($rc) == 2) {
		$log->error("Can't get SMS status.", $rc);
		$ret->error = +$rc[1];
	} else if (Count($rc) == 3) {
		$ret->status = +$rc[0];
		$ret->ts = new DateTime("now");
		if ($rc[1]) {
			$ts = +$rc[1];
			if ($ts) {
				$ret->ts->setTimestamp($ts);
			}
		}

		if ($rc[2]) {
		    $ret->error = +$rc[2];
		}
	} else {
	    $log->error("Wrong answer from SMS provider: ", $rc);
	    $ret->error = 999;
	}
	return $ret;
}

function storeEvent($mailboxId, $messageId, $ts, $eventTypeId, $text) {
	global $log, $db;

	$event = new event;
	$event->MAILBOX_ID = $mailboxId;
	$event->M_ID = $messageId;
	$event->TS = $ts->format('Y-m-d H:i:s');
	$event->EVENT_TYPE_ID = $eventTypeId;
	$event->TEXT = $text;
	$event_id = $event->save();
	if ($event_id != null) {
		return $event_id;
	} else {
		if (Count($event->errors) > 0) {
			$log->error("Can't store EVENT: ", $event->errors);
		} else {
			$log->error("Can't store EVENT: ".$db->getLastError());
		}
		return null;
	}
}

function storeSMSSt($sms_id, $sms_status_i_code = null, $sms_error_i_code = null, $ts = null) {
	global $log, $db;

    $sms_status_id = null;
    $sms_error_id = null;
    if ($sms_status_i_code != null) {
        $sms_status = sms_status::where("I_CODE", $sms_status_i_code)->getOne();
        if ($sms_status instanceof sms_status) {
            $sms_status_id = $sms_status->ID;
        } else {
            $log->error("SMS_STATUS not found. I_CODE=".$sms_status_i_code);
        }
    }
    if ($sms_error_i_code != null) {
        $sms_error = sms_error::where("I_CODE", $sms_error_i_code)->getOne();
        if ($sms_error instanceof sms_error) {
            $sms_error_id = $sms_error->ID;
        } else {
            $log->error("SMS_ERROR not found. I_CODE=".$sms_error_i_code);
        }
    }

    $sms_st = new sms_st;
    $sms_st->SMS_ID = $sms_id;
    if ($sms_status_id != null) {
        $sms_st->STATUS_ID = $sms_status_id;
    }
    if ($sms_error_id != null) {
        $sms_st->ERROR_ID = $sms_error_id;
    }
    $sms_st->TS = $ts?$ts->format('Y-m-d H:i:s'):$db->now();
    $sms_st_id = $sms_st->save();
    if ($sms_st_id) {
        return $sms_st_id;
    } else {
        if (Count($sms_st->errors) > 0) {
            $log->error("Can't store SMS_ST: ", $sms_st->errors);
        } else {
            $log->error("Can't store SMS_ST: ".$db->getLastError());
        }
        return null;
    }
}

function storeSMS($sms_uid, $sms_error, $event_id, $mobile_user_id, $mobile, $text) {
	global $log, $db;

	$sms = new sms;
	$sms->SMS_UID = $sms_uid;
	$sms->EVENT_ID = $event_id;
	$sms->MOBILE_USER_ID = $mobile_user_id;
	$sms->MOBILE = $mobile;
	$sms->TS = $db->now();
	$sms->TEXT = $text;
	$sms_id = $sms->save();

	if ($sms_id != null) {
	    if ($sms_error) {
	        $sms_st_id = storeSMSSt($sms_id, null, $sms_error);
	        if ($sms_st_id) {
	            $log->info("SMS_ST stored. ID=".$sms_st_id);
	        }
	    }
		return $sms_id;
	} else {
		if (Count($sms->errors) > 0) {
			$log->error("Can't store SMS: ", $sms->errors);
		} else {
			$log->error("Can't store SMS: ".$db->getLastError());
		}
		return null;
	}
}

function storeQueueItem($sms_id, $ordernumber, $cloned = 0) {
    global $log, $db;

    $queue = new queue;
    $queue->SMS_ID = $sms_id;
    $queue->ORDERNUMBER = $ordernumber;
    $queue->CLONED = $cloned;
    $queue->TS = $db->now();
    $queue_id = $queue->save();
    if ($queue_id) {
        return $queue_id;
    } else {
        if (Count($queue->errors) > 0) {
            $log->error("Can't store QUEUE item: ", $queue->errors);
        } else {
            $log->error("Can't store QUEUE item: ".$db->getLastError());
        }
        return null;
    }
}

function processMailbox($mailbox) {
	global $log, $db, $mail_host_name, $mail_domain_name, $alarm_event_type_id, $sms_event_type_id;

    $mail_server = new Server($mail_host_name, 143);
    $mail_server->setAuthentication($mailbox->NAME."@".$mail_domain_name, $mailbox->PASSWORD);
    $messages = $mail_server->search("UNSEEN");

    $log->info("Mailbox has ".Count($messages)." new messages.");
    foreach ($messages as $message) {
	    $headers = $message->getHeaders();
	    $from = $message->getAddresses("from");
	    $sender = $headers->sender;
	    $subject = $message->getSubject();
	    if (isValidSubject($subject) || (is_array($sender) && Count($sender) == 1 && isValidSender($sender[0]))) {
			$message_id = $message->getMID();
	        $event = event::where("M_ID", $message_id)->getOne();
	        if ($event instanceof event) {
	    	    $log->info("Event already exists. Message_ID=".$message_id);
	        } else {
	    	    $body = $message->getMessageBody();
	    	    $ts = new DateTime("now");
	    	    $ts->setTimestamp($message->getDate());
			    $event_id = storeEvent($mailbox->ID, $message_id, $ts, $mailbox->QUEUE?$alarm_event_type_id:$sms_event_type_id, $body);
			    if ($event_id) {
	    	        $log->info("Event stored. ID=".$event_id);

				    $mailbox_users = $mailbox->mailbox_users;
				    $mailbox_users_count = $mailbox_users?Count($mailbox_users):0;
				    if (Count($mailbox_users_count > 0)) {
    	    	        $log->info("Mailbox has ".$mailbox_users_count." users.");
				        if ($mailbox_users_count > 1) {
				            usort($mailbox_users, "sortMailboxUsers");
				        }
				        $mobile = $mailbox_users[0]->mobile_user->MOBILE;
				        $name = $mailbox_users[0]->mobile_user->NAME;
				        $log->info("Send SMS to: ".$mobile." (".$name.")");
				        if ($from) {
				        	$body = "".($from["name"]?$from["name"]:$from["address"])."\n".$body;
				        }
				        $body = $body."\n".date("d.m.Y H:i:s", $message->getDate());
				        $rc = sendSMS($mobile, $body);
                        $sms_id = storeSMS($rc->sms_uid, $rc->error, $event_id, $mailbox_users[0]->MOBILE_USER_ID, $mobile, $body);
                        if ($sms_id) {
                            $log->info("SMS stored. ID=".$sms_id);

                            $queue_id = storeQueueItem($sms_id, $mailbox_users[0]->ORDERNUMBER);
                            if ($queue_id) {
                                $log->info("QUEUE item stored. ID=".$queue_id);
                            }
                        }
				    }
			    }
			}
	    }
	    $message->setFlag("Seen");
	}
}

function cloneQueueItem($queue_item) {
    global $log;

    $sms = $queue_item->sms;
    if (!$sms) {
        $log->error("Queue item doesn't have SMS link.");
        return null;
    }
    $event = $sms->event;
    if (!$event) {
        $log->error("SMS doesn't have event link.");
        return null;
    }
    $mailbox = $event->mailbox;
    if (!$mailbox) {
        $log->error("EVENT doesn't have mailbox link.");
        return null;
    }

    $mailbox_users = $mailbox->mailbox_users;
	$mailbox_users_count = $mailbox_users?Count($mailbox_users):0;
	if (Count($mailbox_users_count > 1)) {
        $log->info("Mailbox has ".$mailbox_users_count." users.");
        usort($mailbox_users, "sortMailboxUsers");
        for ($i=0; $i<$mailbox_users_count; $i++) {
            if ($mailbox_users[$i]->ORDERNUMBER == $queue_item->ORDERNUMBER) break;
        }
        if ($i >= $mailbox_users_count-1) {
            $log->info("It was last Mailbox user.");
            return null;
        }
        $mobile = $mailbox_users[$i+1]->mobile_user->MOBILE;
        $name = $mailbox_users[$i+1]->mobile_user->NAME;
        $log->info("Send SMS to: ".$mobile." (".$name.")");
        $sms_text = $sms->TEXT."\nАбонент ".$mailbox_users[$i]->mobile_user->NAME." (".$sms->MOBILE.") недоступен";
        $rc = sendSMS($mobile, $sms_text);
        $sms_id = storeSMS($rc->sms_uid, $rc->error, $sms->EVENT_ID, $mailbox_users[$i+1]->MOBILE_USER_ID, $mobile, $sms_text);
        if ($sms_id) {
            $log->info("SMS stored. ID=".$sms_id);

            $queue_id = storeQueueItem($sms_id, $mailbox_users[$i+1]->ORDERNUMBER);
            if ($queue_id) {
                $log->info("QUEUE item stored. ID=".$queue_id);

                $queue_item->CLONED = 1;
                $rc = $queue_item->save();
                if (!$rc) {
                    if (Count($queue_item->errors) > 0) {
                        $log->error("Can't store QUEUE: ", $queue_item->errors);
                    } else {
                        $log->error("Can't store QUEUE: ".$db->getLastError());
                    }
                }
                return $queue_id;
            }
        }
    } else {
        $log->info("Mailbox has only one user.");
        return null;
    }
}

function processQueueItem($queue_item) {
    global $log;

    $sms = $queue_item->sms;
    if (!$sms) {
        $log->error("Queue item doesn't have SMS link.");
        return -1;
    }
    $event = $sms->event;
    if (!$event) {
        $log->error("SMS doesn't have event link.");
        return -1;
    }
    $mailbox = $event->mailbox;
    if (!$mailbox) {
        $log->error("EVENT doesn't have mailbox link.");
        return -1;
    }
    if (new DateTime("now") - $queue_item->ts > 3600) {
        $log->info("Queue item is too old.");
        return -1;
    }

    $status = getSMSStatus($sms->SMS_UID, $sms->MOBILE);
    $sms_st_id = storeSMSSt($sms->ID, $status->status, $status->error, $status->ts);
    if ($sms_st_id) {
        $log->info("SMS_ST stored. ID=".$sms_st_id);
    }
    if ($status->status == -1) {
        if ($mailbox->QUEUE && !$queue_item->CLONED && new DateTime("now") - $queue_item->ts > 300) {
            $log->info("Try to send SMS to next Mailbox user. Due timeout.");
            $queue_item_id = cloneQueueItem($queue_item);
            if ($queue_item_id) {
                $log->info("QUEUE stored. ID=".$queue_item_id);
            }
        }
    } else if ($status->status == 0 || $status->status == 1 || $status->status == 2) {
        $log->info("SMS delivered to ".$sms->MOBILE);
        return -1;
    } else {
        if (!$mailbox->QUEUE) {
            return -1;
        } else {
            if (!$queue_item->CLONED) {
                $log->info("Try to send SMS to next Mailbox user. Due status error.");
                $queue_item_id = cloneQueueItem($queue_item);
                if ($queue_item_id) {
                    $log->info("QUEUE stored. ID=".$queue_item_id);
                }
            }
            return -1;
        }
    }
}

function processQueue() {
	global $log;

    $log->info("Start processing queue.");
    $queue = queue::get();
    $queue_length = Count($queue);
    $log->info("Queue length: ".$queue_length);
    if ($queue_length > 0) {
        foreach ($queue as $queue_item) {
            $rc = processQueueItem($queue_item);
            if ($rc == -1) {
                $log->info("Delete Queue item. ID=".$queue_item->ID);
                $rc = $queue_item->delete();
                if (!rc) {
                    if (Count($queue_item->errors) > 0) {
                        $log->error("Can't delete Queue item: ", $queue_item->errors);
                    } else {
                        $log->error("Can't delete Queue item: ".$db->getLastError());
                    }
                }
            }
        }
    }
}

function process() {
	global $log;

    $log->info("Start processing.");
    $mailboxes = mailbox::get();
    $mailboxes_count = count($mailboxes);
    $log->info("Found ".$mailboxes_count." Mailboxes.");
    if ($mailboxes_count > 0) {
        foreach ($mailboxes as $mailbox) {
            if ($mailbox instanceof mailbox) {
                $log->info("Start process mailbox: ".$mailbox->NAME);
                processMailbox($mailbox);
            }
        }
    }
    $log->info("Stop process");
}

// Initialization

$log = new Logger(__DIR__.'/logs');
$log->info("Wake up.");

$db = new Mysqlidb('localhost', $db_user_name, $db_password, $db_scheme);
dbObject::autoload("models");

$alarm_event_type_id    = event_type::where("CODE", "ALARM")->getOne()->ID;
$sms_event_type_id      = event_type::where("CODE", "SMS")->getOne()->ID;

// Start processing
$log->info("Process queue.");
processQueue();

$log->info("Process mailboxes.");
process();

$log->info("Sleep.");
$log->info("---------------------------");
// Stop processing
?>