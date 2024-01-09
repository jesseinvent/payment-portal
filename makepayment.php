<?
include('../auth.php');
include('../driver/function.php');
include('../driver/getobjectfunction.php');
include('../pmtfunctions/coslevelinvoice.php');
include('../driver/bigdatafunctions.php');
include('../pmtfunctions/stdacctbalance.php');

include('../js.php');


$msg="";
$orgid=$_SESSION["orgid"];
$acctno=$_SESSION["acctno"];

	$timestamped= new DateTime();
	$timestamped=$timestamped->format('Y-m-d h:i:s');

	$costudytb="alp_scholardb."."courseofstudy";
	$costudyleveltb="alp_scholardb.costudylevel";
	$settb="alp_set.".$orgid;
	$setsemestertb="alp_setsemester.".$orgid;
	$studentstb="alp_students."."studentdetails".$orgid;
	$feesetupcosleveltb="alp_stdpmt."."feesetupcoslevel".$orgid;
	//echo " feesetupcosleveltb = ".$feesetupcosleveltb;
 	//echo " acctno = ".$acctno ;

	$stdrec= result(query("select * from $studentstb where accountid='$acctno' and orgid='$orgid' and status='1' "));
	$acctorgid=$stdrec["acctorgid"];
	$matno=$stdrec["matno"];
	$stdtable=$stdrec["stdtable"];
	$setid=$stdrec["currentsetid"];
	$studentsetsemesteradmitted=$stdrec["studentsetsemesteradmitted"];
	//echo " setsemestertb = ".$setsemestertb ;
	//echo " studentsetsemesteradmitted = ".$studentsetsemesteradmitted;

	$setsemesteradmitted=result(query("select orgsessionrank from $setsemestertb where setsemesterid='$studentsetsemesteradmitted' "));
		$admissionsessionrank=$setsemesteradmitted["orgsessionrank"];
		//echo " admissionsessionrank = ".$admissionsessionrank;
	if(abs($admissionsessionrank) <0) $msg="Your admission details are not complete so the payment process cannot continue ...  ";
		else {
		$details=result(query("select sex from teekler.regusers where accountno='$acctno' "));
		$gender=$details["sex"];
		$country = country($details['country']);
		$state = state($details['state']);
		$lga = lga($details['lga']);
		$setdetails=result(query("select * from $settb where setid='$setid' and status='1' "));
		$setdescription=$setdetails["description"];

		$setsemesterdetails=result(query("select * from $setsemestertb where setid='$setid' and (status='0' or status='1') "));
			$setsemesterid=$setsemesterdetails["setsemesterid"];
			$costudyid=$setsemesterdetails["costudyid"];
			$costudylevelid=$setsemesterdetails["costudylevelid"];
			$orgsemesterid=$setsemesterdetails["orgsemesterid"];
				$orgsemesterdesc=orgSemesterdesc($orgsemesterid);
			$setsessionid=$setsemesterdetails["setsessionid"];    
			$setsessiondesc=getSetsession($setsessionid);
			//echo " costudylevelid = ".$costudylevelid;
			$costudydetails=result(query("select * from $costudytb where costudyid='$costudyid' "));
			$progstudyid=$costudydetails["progstudyid"];
			$progcatid=$costudydetails["progcatid"];
			$progtypeid=$costudydetails["costudytype"];

			$costudyleveldetails=result(query("select * from $costudyleveltb where costudylevelid='$costudylevelid' and status='1' "));
			$costudyleveldescription=$costudyleveldetails["description"];
			$currentsessionrank=$setsemesteradmitted["orgsessionrank"];
			//echo " currentsessionrank = ".$currentsessionrank;
			$currentorgsessionrank=$orgid.$currentsessionrank;
			$currentstdinvoicetb="alp_stdpmt."."invoice".$currentorgsessionrank;
if($orgid!=''){

// create big data
	$orgsessionrankid=$orgid.$currentsessionrank;
$stdpaymentstb="alp_stdpmt."."payments".$orgsessionrankid;
// function checkRemBigdataTableExist is defined in /driver/sql.php
if(checkRemBigdataTableExist($stdpaymentstb) ==0) {
	$query2="CREATE TABLE IF NOT EXISTS ".addslashes($stdpaymentstb) ."(

	paymentid varchar(240) NOT NULL default '',
	accountid varchar(45) DEFAULT NULL,
	orgid varchar(45) DEFAULT NULL,
	progstudyid varchar(45) default NULL,
	progcat varchar(45) DEFAULT NULL,
	progtypeid varchar(30) default NULL,
	costudyid varchar(75) DEFAULT NULL,
	costudylevelid varchar(150) default NULL,
	orgsemesterid varchar(45) default NULL,
	setid	varchar(75) NOT NULL default '',
	setsemesterid varchar(200) NOT NULL default '',
	seqno tinyint(4) default 0,
	amount double default 0,
	createdby varchar(45) NOT NULL,
	timestamped datetime default 0,
	confirmedby varchar(45) NOT NULL,
	timeconfirmed datetime default 0,
	status tinyint(4) default 0,
	PRIMARY KEY (paymentid)

	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$create=query($query2)or die(mysql_error()."error function payment function");
} // if(!checkRemBigdataTableExist($stdpaymentstb))

}  //  if($orgid!=''){

if(isset($_POST['makepmt'])){
	$amount=$_POST["amt"];
	$getsno=result(query("select max(seqno) as sno from $stdpaymentstb where accountid='$acctno' "));
	$seqno=$getsno['sno'];
	if($seqno<1) $nextseqno=1; else $nextseqno=$seqno+1;
	$paymentid=$acctno.$setsemesterid.$nextseqno;
	//echo " seqno = ".$seqno;
	//echo " nextseqno = ".$nextseqno;

/*
	echo " paymentid = ".$paymentid;
	echo " accountid = ".$accountid;
	echo " orgid = ".$orgid;
	echo " progstudyid = ".$progstudyid;
	echo " progcatid = ".$progcatid;
	echo " progtypeid = ".$progtypeid;
	echo " costudyid = ".$costudyid;
	echo " costudylevelid = ".$costudylevelid;
	echo " orgsemesterid = ".$orgsemesterid;
	echo " setid = ".$setid;
	echo " setsemesterid = ".$setsemesterid;
	echo " amount = ".$amount;
	echo " acctno = ".$acctno;
	echo " timestamped = ".$timestamped;
*/
	$insertpmtamount=query("INSERT INTO $stdpaymentstb(`paymentid`, `accountid`, `orgid`, `progstudyid`,
		 `progcat`, `progtypeid`, `costudyid`, `costudylevelid`, `orgsemesterid`, `setid`, `setsemesterid`,seqno,
		 `amount`, `createdby`, `timestamped`, `confirmedby`, `timeconfirmed`, `status`)
	VALUES ('$paymentid','$acctno','$orgid','$progstudyid',
		'$progcatid','$progtypeid','$costudyid','$costudylevelid','$orgsemesterid','$setid','$setsemesterid','$nextseqno',
		'$amount','$acctno','$timestamped','','0000-00-00','1')");

	//echo " insertpmtamount = ".$insertpmtamount;
	if ($insertpmtamount) $msg="Payment successful"; else $msg="Payment failed";

		// include codes to report success or failure of operation
}  //  if(isset($_POST['makepmt'])){
}  //  if(abs($admissionsessionrank) <0)
?>

<!DOCTYPE html>
<!-- saved from url=(0091)http://www.jqueryscript.net/demo/Windows-10-Style-Animated-Navigation-Box-with-jQuery-CSS3/ -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Teekler - Org | <?php echo shortenOrgname($_SESSION['orgid']); ?></title>
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<style>
@import url(http://fonts.googleapis.com/css?family=Open+Sans);

* {
  margin: 0;
  padding: 0;
  font-size: inherit;
  color: inherit;
  box-sizing: inherit;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  -webkit-font-smoothing: antialiased;
}

*:focus { outline: none; }

html { box-sizing: border-box; }

body {
  background-color: #ecf0f1;
  min-width: 300px;
  font-family: 'Open Sans', sans-serif;
  font-size: 16px;
}

h1, h2, h3, h4, h5 {
  display: block;
  font-weight: 400;
}

li, span, p, a, h1, h2, h3, h4, h5 { line-height: 1; }

p { display: block; }

a { text-decoration: none; }

a:hover { text-decoration: underline; }

img {
  display: block;
  max-width: 100%;
  height: auto;
  border: 0;
}

button {
  background-color: transparent;
  border: 0;
  cursor: pointer;
}

/* Reset */


html, body { height: 100%; }

.navigation-bar, .navigation-bar .navbox-tiles, .navbox-trigger, .navbox-tiles .tile, .navbox-tiles .tile .icon .fa, .navbox-tiles .tile .title {
  -webkit-transition: all .3s;
  transition: all .3s;
}

.navbox-tiles:after {
  content: '';
  display: table;
  clear: both;
}

/* Core Styles */


.navigation-bar {
  height: 80px;
  position: relative;
  z-index: 1000;
}

.navigation-bar .bar {
  background-color: #252525;
  width: 100%;
  height: 100%;
  position: absolute;
  z-index: 2;
}

.navigation-bar .navbox {
  visibility: hidden;
  opacity: 0;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1;
  -webkit-transform: translateY(-200px);
  -ms-transform: translateY(-200px);
  transform: translateY(-200px);
  -webkit-transition: all .2s;
  transition: all .2s;
}

.navigation-bar .navbox-tiles {
  -webkit-transform: translateY(-200px);
  -ms-transform: translateY(-200px);
  transform: translateY(-200px);
}

.navigation-bar.navbox-open .navbox-trigger { background-color: #F44336; }

.navigation-bar.navbox-open .navbox {
  visibility: visible;
  opacity: 1;
  -webkit-transform: translateY(0);
  -ms-transform: translateY(0);
  transform: translateY(0);
  -webkit-transition: opacity .3s, -webkit-transform .3s;
  transition: opacity .3s, transform .3s;
}

.navigation-bar.navbox-open .navbox-tiles {
  -webkit-transform: translateY(0);
  -ms-transform: translateY(0);
  transform: translateY(0);
}

.navbox-trigger {
  background-color: transparent;
  width: 50px;
  height: 50px;
  line-height: 50px;
  text-align: center;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.navbox-trigger .fa {
  font-size: 20px;
  color: #fff;
}

.navbox-trigger:hover { background-color: #484747; }

.navbox {
  background-color: #484747;
  width: 100%;
  max-width: 380px;
  -webkit-backface-visibility: initial;
  backface-visibility: initial;
}

.navbox-tiles {
  width: 100%;
  padding: 25px;
}

.navbox-tiles .tile {
  display: block;
  background-color: #3498db;
  width: 30.3030303030303%;
  height: 0;
  padding-bottom: 29%;
  float: left;
  border: 2px solid transparent;
  color: #fff;
  position: relative;
}

.navbox-tiles .tile .icon {
  width: 100%;
  height: 100%;
  text-align: center;
  position: absolute;
  top: 0;
  left: 0;
}

.navbox-tiles .tile .icon .fa {
  font-size: 35px;
  position: absolute;
  top: 50%;
  left: 50%;
  -webkit-transform: translate(-50%, -50%);
  -ms-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
  -webkit-backface-visibility: initial;
  backface-visibility: initial;
}

.navbox-tiles .tile .title {
  padding: 5px;
  font-size: 12px;
  position: absolute;
  bottom: 0;
  left: 0;
}


.navbox-tiles .tile:hover {
  border-color: #fff;
  text-decoration: none;
}
.navbox-tiles .tile:not(:nth-child(3n+3)) {
 margin-right: 4.54545454545455%;
}

.navbox-tiles .tile:nth-child(n+4) { margin-top: 15px; }
 @media screen and (max-width: 370px) {

.navbox-tiles .tile .icon .fa { font-size: 25px; }

.navbox-tiles .tile .title {
  padding: 3px;
  font-size: 11px;
}
}
</style>

<script type="applijegleryion/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<link href="../css/bootstrap.css" rel='stylesheet' type='text/css' />
<!-- Custom Theme files -->
<link href="../css/style.css" rel='stylesheet' type='text/css' />	
<script src="../js/jquery-1.11.1.min.js"></script>
<!-- start menu -->
<link href="../css/megamenu.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="../js/megamenu.js"></script>
<script>$(document).ready(function(){$(".megamenu").megamenu();});</script>
<script src="../js/menu_jquery.js"></script>
<script src="../js/simpleCart.min.js"> </script>
<!--web-fonts-->
 <link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,300italic,600,700' rel='stylesheet' type='text/css'>
 <link href='//fonts.googleapis.com/css?family=Roboto+Slab:300,400,700' rel='stylesheet' type='text/css'>
<!--//web-fonts-->
 <script src="../js/scripts.js" type="text/javascript"></script>
<script src="../js/modernizr.custom.js"></script>
<script type="text/javascript" src="../js/move-top.js"></script>
<script type="text/javascript" src="../js/easing.js"></script>
<!--/script-->
<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(".scroll").click(function(event){		
					event.preventDefault();
					$('html,body').animate({scrollTop:$(this.hash).offset().top},900);
				});
			});
</script>
<!-- the jScrollPane script -->
<script type="text/javascript" src="../js/jquery.jscrollpane.min.js"></script>
		<script type="text/javascript" id="sourcecode">
			$(function()
			{
				$('.scroll-pane').jScrollPane();
			});

  function selectDiv(imageno,code,callingcode){
	var image="url(images/flags4.png) 0 "+ Number(imageno)+"px";
	document.getElementById('selflag').style.background=image;
	document.getElementById('selflag').innerHTML="";
	document.getElementById('divflag').style.display="none";
	document.getElementById('phone').value=callingcode;
	}
	function displayDiv(){
	document.getElementById('divflag').style.display="block";
	}
	function checkPlus(){
		var myphone=document.getElementById('phone').value;
		var plus=myphone[0];
		if(plus!='+'){
		alert("Please Enter A Country Code in the phone number");
		document.getElementById('phone').value="";}
		}

function showCalc(id, amount){
	balance = parseInt(document.getElementById("pursebalance").value);
	total = parseInt(document.getElementById("total").innerHTML);
	selected = parseInt(document.getElementById("selected").innerHTML);
	if(document.getElementById(id).checked){
		
		selected = parseInt(selected) + parseInt(amount);
		total = parseInt(total) - parseInt(amount);
	} else{
		selected = parseInt(selected) - parseInt(amount);
		total = parseInt(total) + parseInt(amount);
	}
	if(selected > balance){
		warningAlert("WARNING!", "The School is owing! Check payments menu to confirm! Make additional payments to increase the purse balance or reduce the number of active students");
		return false;
	}
	else{
	document.getElementById("total").innerHTML = total;
	document.getElementById("selected").innerHTML = selected;
	return true;
	}
}
		</script>
<!-- //the jScrollPane script -->

</head>

<body>
<?php include('../topside.php'); ?><?php include('../menu.php'); ?>

<div class="container" style="width:100%; padding:0px;">
<div class="column-left" style="margin:0px;"><?php include('../leftside.php'); ?></div>
<div class="column-center" style="background:#FFF; margin:0px">
 <form method="post" name="form">
<p align="center" style="font-size:16px;color:<?php echo $error==0?"#F00":"#00F" ?>"><?php echo $msg!=""?$msg:"" ?></p>
<?php
			// genstdinvoice generates invoice and is defined in include('../pmtfunctions/coslevelinvoice.php')
			 $currentinvamt=gencoslevelinvoice($acctno,$orgid,$currentsessionrank);
			//echo " coslevelinvgensuccessful = ".$coslevelinvgensuccessful;

			// genstdacctbal generates student account balance and is defined in include('../pmtfunctions/stdacctbalance.php')
			$stdacctbalance=genstdacctbal($acctno,$orgid);


 	//echo " acctno = ".$acctno ;
	$stddet= query("select * from $studentstb where accountid='$acctno' and orgid='$orgid' and status='1' ");
if(rows($stddet)<1) $interfacemsg="You are not an active student";
  else {
?>
  <table width="100%">
<tr>
<td><strong>STUDENT ID.</strong></td>
<td><?php echo $matno; ?></td>
<td><strong>NAME</strong></td>
<td><?php echo usersName($acctno); ?></td>
</tr>
<tr>
<td><strong>SET</srong></td>
<td><?php echo $setdescription; ?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>

<tr>
<td><strong>SESSION</srong></td>
<td><?php echo $setsessiondesc; ?></td>
<td><strong>SEMESTER</srong></td>
<td><?php echo $orgsemesterdesc; ?></td>
</tr>
  </table>

<?php
// confirm current invoice was generated
$getcurinvoice=rows(query("select * from $currentstdinvoicetb where accountid='$acctno'
	and costudylevelid='$costudylevelid' and orgsemesterid='$orgsemesterid' "));
	//echo " getcurinvoice = ".$getcurinvoice;
if($getcurinvoice <1) $interfacemsg="No invoice has been generated for the current semester so account balance may not be correct ";
?>
  <table width="100%">
<td>&nbsp;</td>
    <tr>
<td width="40%"><strong>CURRENT INVOICE AMOUNT</strong></td>
<td colspan="3"><?php echo number_format($currentinvamt,2); ?></td>
<td><a href="stdstatementacct.php?statement=<?php echo $matno;?> & setid=<?php echo $setid;?> & gender=<?php echo $gender;?>"><input type="button" value=" View Statement of Accounts " name="statement"  /></a></td>
</tr>
<?php
if($stdacctbalance <0){
?>
    <tr>
<td width="40%"><strong>OUTSTANDING FEE</strong></td>
<td colspan="3"><?php echo number_format($stdacctbalance,2); ?></td>
</tr>
<?php
} else if($stdacctbalance >0){
?>
<tr>
<td width="40%"><strong>PURSE BALANCE</strong></td>
<td colspan="3"><?php echo number_format($stdacctbalance,2); ?></td>
</tr>
<?php
 } else {
?>
    <tr>
<td width="40%"><strong>OUTSTANDING FEE</strong></td>
<td colspan="3"><?php echo number_format(0,2); ?></td>
</tr>
<?php
} //  if($stdacctbalance <0){
?>
</table>

<table width="100%">
<td>&nbsp;</td>
    <tr>
<td align ="center"><strong>SELECT PAYMENT ACTIVITY</strong></td>
</tr>
<tr>
<td><a href="makepayment.php?payonline=<?php echo $matno;?> & setid=<?php echo $setid;?> & gender=<?php echo $gender;?>"><input type="button" value=" Pay Online " name="online"  /></a></td>
<td>&nbsp;</td>
</tr>

<tr>
<td>&nbsp;</td>
<td><a href="makepayment.php?doctransfer=<?php echo $matno;?> & setid=<?php echo $setid;?> & gender=<?php echo $gender;?>"><input type="button" value=" Document Transfer " name="transfer"  /></a></td>
</tr>

<tr>
<td><a href="makepayment.php?bankdep=<?php echo $matno;?> & setid=<?php echo $setid;?> & gender=<?php echo $gender;?>"><input type="button" value=" Document Bank Deposit " name="bank"  /></a></td>
<td>&nbsp;</td>
</tr>
<td>&nbsp;</td>

<?php
//payonline, document cash payment, satisfy invoice
if(isset($_GET['payonline'])){
	// generate invoice
	$setsemesterdetails=result(query("select * from $setsemestertb where setid='$setid' and (status='0' or status='1') "));
		$setsemesterid=$setsemesterdetails["setsemesterid"];
		$costudyid=$setsemesterdetails["costudyid"];
		$costudylevelid=$setsemesterdetails["costudylevelid"];
		$orgsemesterid=$setsemesterdetails["orgsemesterid"];
			$orgsemesterdesc=orgSemesterdesc($orgsemesterid);
		$setsessionid=$setsemesterdetails["setsessionid"];
			$setsessiondesc=getSetsession($setsessionid);
	//echo " costudytb = ".$costudytb  ;
	//echo " costudyid = ".$costudyid;
$costudydetails=result(query("select * from $costudytb where costudyid='$costudyid'  "));
	$progstudyid=$costudydetails["progstudyid"];
	$programmetype=$costudydetails["costudytype"];
	$programmecategory=$costudydetails["progcatid"];

	//echo " scope1 = ".$scope1;
	//echo " scope2 = ".$scope2;
	//echo " scope3 = ".$scope3;
?>


  <table width="60%">
<td>&nbsp;</td>

<tr>
<td align ="left"><strong>ENTER AMOUNT</strong></td>
<td><input type="float" min="1" name="amt" placeholder='type amount' required/></td>
</tr>
<tr>
<td>&nbsp;</td>
 <td width="50%"> <input onClick="return showCalc('<?php echo $i; ?>', '<?php echo $invoiceamount; ?>')" type="checkbox" name="invoiceid<?php echo $i; ?>" id="<?php echo $i; ?>" value="<?php echo $invoicedetails['cos_invoiceid']?>"/>
<td>&nbsp;</td>
   <td colspan="10" style="font-size:15px" align="left"><a href="checkout.php?orgunitid=<?php echo $orgunitid.'&locationoption='.$locationid.'&reporttype='.$reporttypeid.'&stafflisttb='.$stafflisttb; ?>" target="_new" style="color: blue">View Report</a></td>
</tr>
</table>
<?php
}  //  if(isset($_GET['payonline'])){
}  //  if(rows($stddet)<1)
 ?>
<p align="center" style="font-size:16px;color:<?php echo $error==0?"#F00":"#00F" ?>"><?php echo $interfacemsg!=""?$interfacemsg:"" ?></p>
</table>
</form>
 <iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="popcalendar/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
        </iframe>
</div>
   <div class="column-right" style=" margin:0px;"><?php include('../rightside.php'); ?></div>
</div>
<div class="footer" style="background:#333; height:100px;">
		<div class="" style="background:#333">
			<div class="footer-grid" style="background:#333">
				<div class="footer-grid-center" style="background:#333">
					<p style="color:#FFF" align="center">Copyright © 2016 Teekler</p>
				</div>
				
				<div class="clearfix"> </div>
			</div>
		</div>
	</div>
<script src="../js/megamenu.js"></script>

<script>
(function () {
    $(document).ready(function () {
        $('#navbox-trigger').click(function () {
            return $('#navigation-bar').toggleClass('navbox-open',1000, "easeOutCubic");
        });
        return $(document).on('click', function (e) {
            var $target;
            $target = $(e.target);
            if (!$target.closest('.navbox').height && !$target.closest('#navbox-trigger').height) {
                return $('#navigation-bar').removeClass('navbox-open');
            }
        });
    });
}.call(this));
</script>

</body>
</html>