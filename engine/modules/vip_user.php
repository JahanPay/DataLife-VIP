<?PHP

/*
Masoud Amini . Devs for jahanpay and bug fixed by mohammadN . Devs for jahanpay and bug fixed by mohammadN
*/

if( ! defined('DATALIFEENGINE') ) {
	die( "Hacking attempt!" );
}

  $this_time = time() + ($config['date_adjust'] * 60);


/*//   پرداخت
####################################################################################################################
*/


	if ( $member_id['vip_approve'] == 0 ) {


  if ( $doaction == "ok") {

	  $orderid = $_GET['order_id'];
	 // $price = $_GET['price'];
	  $rejahanpay = $db->super_query("SELECT * FROM ".PREFIX."_vip_jahanpay where id = '$orderid'");
	  $res_plan = $db->super_query("SELECT * FROM ".PREFIX."_vip_panel where id='$rejahanpay[vip_panel]'");
   	@require_once ROOT_DIR . '/engine/classes/nusoap/nusoap.php';
	  $MID = $setting_res['marchentid'];
	  $time_end = time()+60*60*24*30*$res_plan['plantme'];
	  $this_time = time() + ($config['date_adjust'] * 60);
      $price = $rejahanpay['price'];
      $au = $rejahanpay['au'];
	  $date_jahanpay = jdate('Y/m/d H:m');
	  $view_endTIME = jdate('Y/m/d', $time_end);
	  
	$setting_res = $db->super_query("SELECT * FROM ".PREFIX."_vip_setting where id = '1'");
	
	$client = new SoapClient("http://www.jpws.me/directservice?wsdl");
	$res = $client->verification($MID , $price , $au , $orderid, $_POST + $_GET );
if( ! empty($res['result']) and $res['result'] == 1)
				{

		  $result_payment = "<div class=\"success\">
		  	پرداخت و عضویت VIP شما با موفقیت انجام گردید.
			<br>

			<table width=\"100%\">
				<tr>
				 	<td> پلان انتخابی: </td>
					<td> $res_plan[name] </td>
				</tr>
				<tr>
				 	<td> تاریخ شروع عضویت: </td>
					<td> $date_jahanpay </td>
					<td></td>
				</tr>

				<tr>
				 	<td> تاریخ اتمام عضویت: </td>
					<td> $view_endTIME </td>
				</tr>


				<tr>
				 	<td> مبلغ واریزی :  </td>
					<td> $rejahanpay[price] </td>
				</tr>


			</table>

		  </div>";



  	$db->query( "UPDATE " . PREFIX . "_vip_jahanpay set `au`='".$au."',`res`='".$result."', `date`='".$date_jahanpay."', `vip_time`='".$time_end."', `show`='1' where userid='$member_id[user_id]'  limit 1");
	
	$db->query( "UPDATE " . PREFIX . "_users set `viptime_plan`='".$time_end."', `viptime_start`='".$this_time."' where user_id='$member_id[user_id]' limit 1");

	$db->query( "UPDATE " . PREFIX . "_users set `user_group`='".$setting_res['group_id']."' where user_id='$member_id[user_id]' limit 1");


$res2= $res["result"];
	  } else {
		$result_payment = "  <div class=\"success\">
			خطا در پرداخت :  &nbsp;&nbsp; $res2
			<br>
			لطفا مجددا تلاش نمایید.

		  </div>";

		  //$db->query( "DELETE FROM " . PREFIX . "_vip_jahanpay WHERE userid='$member_id[user_id]' and au='$au' and res!='1' limit 1" );

	  }






	$tpl->set( '{result}', $result_payment);
	$tpl->load_template( 'vip_success.tpl' );
	$tpl->compile( 'content' );
	$tpl->clear();


/*//   پرداخت
####################################################################################################################
*/

  } elseif ( $doaction == "payment" ) {

	  	if ( empty( $_POST['vipradio'])) {
			msgbox("خطا !"," گزینه ای برای پرداخت انتخاب نشده است.");
		} else {
		$id = intval($_POST['vipradio']);

	  	$select_row = $db->super_query("SELECT * FROM ".PREFIX."_vip_panel where id = '$id' limit 1");
	  	$setting_res = $db->super_query("SELECT * FROM ".PREFIX."_vip_setting where id = '1'");

	  @require_once ROOT_DIR . '/engine/classes/nusoap/nusoap.php';
	  
	  $MID = $setting_res['marchentid'];
	  $price = $select_row['price'];
	  	  $db->query( "INSERT INTO " . PREFIX . "_vip_jahanpay set `userid`='".$member_id['user_id']."', `vip_panel`='".$id."', `au` = '".$res."', `price`='".$price."', `show`='0'");
          $insert_id = $db->insert_id();
          $GLOBALS["RedirectURL"] = "".$config['http_home_url']."index.php?do=vip_user&doaction=ok&price=". $select_row['price']."&order_id=". $insert_id."";
          
        
			$client = new SoapClient("http://www.jpws.me/directservice?wsdl");
			$res = $client->requestpayment($MID, $price, $GLOBALS["RedirectURL"] , $insert_id);	
 if($res['result']==1){
 $db->query( "UPDATE " . PREFIX . "_vip_jahanpay set `au`='".$res['au']."' where id='$insert_id'  limit 1 ");
            echo ('<div style="display:none;">'.$res['form'].'</div><script>document.forms["jahanpay"].submit();</script>');
	      } else {
              
                    		msgbox("خطا !"," خطا در اتصال به درگاه");

                }



	  echo '
	
	';


		}




/*//   خروجی پلان ها
####################################################################################################################
*/

  } else {







    $query = $db->query("SELECT * FROM ".PREFIX."_vip_panel order by id desc");
	while ( $row = $db->get_row($query))  {
		$price = number_format($row['price']);
		$list_panel .= "<label for=\"da$row[id]\"><li><input type=\"radio\" id='da$row[id]' name=\"vipradio\" value=\"".$row['id']."\"> $row[name] &nbsp; $price تومان </li>";

	}

/*
	@$db->query("ALTER TABLE `" . PREFIX . "_users` ADD `viptime_start` INT( 11 ) NOT NULL AFTER `news_num`,
	ADD `viptime_plan` INT( 11 ) NOT NULL AFTER `news_num`");

*/




	$ON_FORM = "<form method=\"post\" action=\"".$config['http_home_url']."index.php?do=vip_user&doaction=payment\" enctype=\"multipart/form-data\">";  /* شروع فرم */
	$END_FORM = "</form>"; /* اتمام فرم */

    $tpl->set( '{payerror}', $payerror);
	$tpl->set( '{form start}', $ON_FORM);
	$tpl->set( '{end form}', $END_FORM);
	$tpl->set( '{لیست پنل‏ها}', $list_panel);
	$tpl->load_template( 'vip_user.tpl' );
	$tpl->compile( 'content' );
	$tpl->clear();
  }
  } else {
  msgbox("خطا", "شما عضو VIP بوده و قادر به پرداخت و عضويت VIP مجدد نميباشيد. در صورت هرگونه مشکل با مديريت تماس حاصل نماييد."
  );
  }

?>