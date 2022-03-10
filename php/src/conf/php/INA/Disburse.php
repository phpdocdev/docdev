<?
        function disburseOrders($service, $amount, $sd, $ed, $text, $orders, $emailSubj, $emailCC, $sendInstructions=''){
                require_once('DB.php');
                $DB = DB::connect('mysql://customer@db.dev.ark.org/customer', false);
                $DB->setFetchMode(DB_FETCHMODE_ASSOC);

                $type = 'Debit';

                # create a record in disburse
                $sendInstructions = mysql_escape_string($sendInstructions);      
                $sql = "insert into disburse(req_date, start_date, end_date, descr, amount, type, service,send_instructions)values(NOW(), '$sd', '$ed', '".mysql_escape_string($text)."', $amount, '$type', '$service', '$sendInstructions')";
                //echo "$sql\n";
                $DB->query($sql);
                $did = $DB->getOne("select last_insert_id()");
                $cnt = 0;
                foreach( $orders as $order){
                        $sql = "insert into disburse_orders(id, orderid)values($did, '$order')";
                        //echo "$sql\n";
                        $DB->query($sql);
                        $cnt++;
                }
                $DB->query("update disburse set order_count=$cnt where id=$did");

                $header = "From: support@ark.org";
                //$header .= "Content-Type: text/html; charset=iso-8859-1;\r\n\r\n";

                if( $amount > 0 ){
                        mail('neo@ark.org', $emailSubj, $text, $header);
                        if( $emailCC ){
                                mail($emailCC, $emailSubj, $text, $header);
                        }
                }

        }

        function returnOrders($service, $amount, $sd, $ed, $text, $orders, $emailSubj, $emailCC, $sendInstructions=''){
                require_once('DB.php');
                $DB = DB::connect('mysql://customer@db.dev.ark.org/customer', false);
                $DB->setFetchMode(DB_FETCHMODE_ASSOC);

                $type = 'Credit';

                # create a record in disburse
                $sendInstructions = mysql_escape_string($sendInstructions);
                $sql = "insert into disburse(req_date, start_date, end_date, descr, amount, type, service,send_instructions)values(NOW(), '$sd', '$ed', '".mysql_escape_string($text)."', $amount, '$type', '$service', '$sendInstructions')";
                //echo "$sql\n";
                $DB->query($sql);
                $did = $DB->getOne("select last_insert_id()");
                $cnt = 0;
                foreach( $orders as $order){
                        $sql = "insert into disburse_orders(id, orderid)values($did, '$order')";
                        //echo "$sql\n";
                        $DB->query($sql);
                        $cnt++;
                }
                $DB->query("update disburse set order_count=$cnt where id=$did");

                $header = "From: support@ark.org";
                //$header .= "Content-Type: text/html; charset=iso-8859-1;\r\n\r\n";

                if( $amount > 0 ){
                        mail('neo@ark.org', $emailSubj, $text, $header);
                        if( $emailCC ){
                                mail($emailCC, $emailSubj, $text, $header);
                        }
                }

        }
?>
