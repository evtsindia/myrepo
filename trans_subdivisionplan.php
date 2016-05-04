<?php
include "../../../../../wp-config.php";
global $wpdb; 

$funct = $_GET['funct'];
$page = $_GET['page'];
if($funct == 'fetch_subdivisionplan' && $page == 'choose_your_home'){
	$subid = $_POST['subid'];
	$data = array();
	$sql = "SELECT
			a.id as subid,
			a.code,
			b.planname,
			b.id as planid,
			b.subdivision_code,
			b.description,
			c.subdivisionplanid,
			c.filename
			from builder_subdivision a
			left join builder_subdivisionplan b on b.subdivision_code = a.`code`
			left join builder_subdivisionattachment c on c.subdivisionplanid = b.id
			where 
			a.id = $subid
			group by b.id";
	$result = $wpdb->get_results($sql);
	$html = '<table class="wwb_table" style="width: 100%;">';
	$subcount = 0;
	$open_close = 0;
	
	foreach($result as $key => $obj){
		if($subcount == 0){$html .= '<tr><td class="wwb_table_td">'; $open_close = 1;}
		$html .= '<div class="wwb_div" style="width: 33.33%; float: left; padding: 5px;">
					   <img class="wwb_img" src="http://www.salessimplicity.net/Livedemo/attachments/'.$obj->filename.'" style="width: 100%; height: 200px;">
					   <h4 class="wwb_h4">'.$obj->planname.'</h4>
					   <p>'.$obj->description.'</p>
					   <p><input type="button" onClick="chooseNext(2,'.$obj->planid.')" value="Select Plan" class="fp_view" /></p>
				  </div>';
		$subcount++;
		if($subcount == 3){$html .= '</td></tr>'; $subcount=0; $open_close = 0;}
	}
	if($open_close == 1){$html .= '</td></tr>';}
	$html .= '</table>';
	
	@session_start();
	if(@$_SESSION['islogin']){ $login = true; }else{ $login = false; }
	
	die(json_encode(array('success' => true, 'plan' => $html, 'islogin' => $login)));
}

if($funct == 'register_user' && $page == 'choose_your_home')
{
	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	$phone = $_POST['phone'];
	$email = $_POST['email'];
	$password = md5($_POST['passw']);
	$subdivisionid = $_POST['subdivisionid'];
	
	$check_email = $wpdb->get_results("select * from builder_user where email = '$email' and status = 1 ");
	if(count($check_email) == 0){
		$wpdb->insert( 
			'builder_user', 
			array( 
				'firstname' => $fname, 
				'lastname' => $lname, 
				'phone' => $phone, 
				'email' => $email, 
				'password' => $password, 
				'status' => 1, 
				'date_added' => date('Y-m-d H:i:s')
			)
		);
		
		$html = get_unit_data($subdivisionid,0);
		$topodata = get_phases_data($subdivisionid);
		
		$topocount = 0;
		$mapViewer = '<div id="topo-mapper" style="padding: 10px;">';
		$tabs = '<table class="topo_map_table"><tr>';
		
		foreach($topodata as $key => $val){
			
			if($topocount == 0){
				$tpimg = get_subdivision_topo($subdivisionid);
				$mapViewer .= '<img src="'.$tpimg.'">';
				$tabs .= '<td class="topo_map_tab"><a href="javascript: void(0)" onClick="viewTopoMap(\''.$tpimg.'\',0)">All</a></td>';	
			}
			
			$tabs .= '<td class="topo_map_tab"><a href="javascript: void(0)" onClick="viewTopoMap(\''.$val['topoimage'].'\','.$val['phaseid'].')">'.$val['phase_name'].'</a></td>';							
			$topocount++;
			
		}
		$mapViewer .= '</div>';
		$tabs .= '</tr></table>'.$mapViewer;
		
		$lothtml = '<div id="unitViewer">'.$html['html'].'</div>';
		
		@session_start();
		$_SESSION['islogin'] = true;
		$_SESSION['useremail'] = $email;
		
		die(json_encode(array("success" => true, "email" => $email, "htmldata" => $lothtml, "topo_img" => $tabs)));
	}else{
		die(json_encode(array("success" => false, "msg" => "Email already registered, please use another email or you can retrieve your password.")));
	}
}

if($funct == 'login' && $page == 'choose_your_home')
{
	$username = $_POST['username'];
	$password = md5($_POST['password']);
	$subdivisionid = $_POST['subdivisionid'];
	
	$islogin = $wpdb->get_results("select * from builder_user where email = '$username' and password = '$password' and status = 1");
	if(count($islogin) > 0){
		
		@session_start();
		$_SESSION['islogin'] = true;
		$_SESSION['useremail'] = $username;
		
		$html = get_unit_data($subdivisionid,0);
		
		$topodata = get_phases_data($subdivisionid);
		
		$topocount = 0;
		$mapViewer = '<div id="topo-mapper" style="padding: 10px;">';
		$tabs = '<table class="topo_map_table"><tr>';
		
		foreach($topodata as $key => $val){
			
			if($topocount == 0){
				$tpimg = get_subdivision_topo($subdivisionid);
				$mapViewer .= '<img src="'.$tpimg.'">';
				$tabs .= '<td class="topo_map_tab"><a href="javascript: void(0)" onClick="viewTopoMap(\''.$tpimg.'\',0)">All</a></td>';	
			}
			
			$tabs .= '<td class="topo_map_tab"><a href="javascript: void(0)" onClick="viewTopoMap(\''.$val['topoimage'].'\','.$val['phaseid'].')">'.$val['phase_name'].'</a></td>';							
			$topocount++;
			
		}
		$mapViewer .= '</div>';
		$tabs .= '</tr></table>'.$mapViewer;
		
		$lothtml = '<div id="unitViewer">'.$html['html'].'</div>';
		
		die(json_encode(array("success" => true, "email" => $username, "htmldata" => $lothtml,  "topo_img" => $tabs)));
		
	}else{
		
		die(json_encode(array("success" => false, "msg" => "Username and Password did not match.")));
			
	}
}


if($funct == 'fp' && $page == 'choose_your_home')
{
	$username = trim($_POST['username']);
	$subdivisionid = trim($_POST['subdivisionid']);
	
	$islogin = $wpdb->get_results("select * from builder_user where email = '$username' and status = 1");
	if(count($islogin) > 0){
		
		//get site details
		$sql_site_details = $wpdb->get_results("select * from wp_options");		
		$siteurl = get_option( 'siteurl' );
		$blogname = get_option( 'blogname' );
		$admin_email = get_option( 'admin_email' );
		
		@session_start();
		$fp_token = md5(rand(1,5));
		setcookie('fptoken',$fp_token,time()+5*60*60,'/');
		
		//update cookie 
      	$execute = $wpdb->query( $wpdb->prepare( "UPDATE builder_user SET forgot_password_token ='".$fp_token."' WHERE email ='".$username."'") );
		
		
		
		
		$message = '<div>
			<p>Please use the follwoing link to <a href="'.$siteurl.'/reset-password?u='.$username.'&t='.$fp_token.'">reset your password</a></p>
			<p>If you did not request this password change please feel free to ignore it.</p>
			<p>Please feel free to respond to this email.</p>
		</div>';
		
		//send mail to user
		$subject = $blogname.' Password Reset';
		
		$headers = "From: $blogname <$admin_email>\r\n". 
		   "MIME-Version: 1.0" . "\r\n" . 
		   "Content-type: text/html; charset=UTF-8" . "\r\n"; 
			   
		mail($username,$subject,$message,$headers);
		
		die(json_encode(array("success" => true, "email" => $username)));
		
	}else{
		
		die(json_encode(array("success" => false, "msg" => "Username did not match.")));
			
	}
}
if($funct == 'login')
{
	$username = $_POST['username'];
	$password = md5($_POST['password']);
	$subdivisionid = $_POST['subdivisionid'];
	
	$islogin = $wpdb->get_results("select * from builder_user where email = '$username' and password = '$password' and status = 1");
	if(count($islogin) > 0){
		
		@session_start();
		$_SESSION['islogin'] = true;
		$_SESSION['useremail'] = $username;
		
		$link = get_site_url()."/builderux-choose-your-home/";
		
		die(json_encode(array("success" => true, "email" => $username, "link" => $link)));
		
	}else{
		
		die(json_encode(array("success" => false, "msg" => "Username and Password did not match.")));
			
	}
}
if($funct == 'logout')
{
	@session_start();
	$_SESSION['islogin'] = false;
	$_SESSION['useremail'] = '';
	$link = get_site_url()."/builderux-choose-your-home/";
	die(json_encode(array("success" => true, "link" => $link)));
}

if($funct == 'direct_unit_link')
{
		$subdivisionid = $_POST['subid'];
		$html = get_unit_data($subdivisionid,0);
		
		$topodata = get_phases_data($subdivisionid);
		
		$topocount = 0;
		$mapViewer = '<div id="topo-mapper" style="padding: 10px;">';
		$tabs = '<table class="topo_map_table"><tr>';
		
		foreach($topodata as $key => $val){
			
			if($topocount == 0){
				$tpimg = get_subdivision_topo($subdivisionid);
				$mapViewer .= '<img src="'.$tpimg.'">';
				$tabs .= '<td class="topo_map_tab"><a href="javascript: void(0)" onClick="viewTopoMap(\''.$tpimg.'\',0)">All</a></td>';	
			}
			
			$tabs .= '<td class="topo_map_tab"><a href="javascript: void(0)" onClick="viewTopoMap(\''.$val['topoimage'].'\','.$val['phaseid'].')">'.$val['phase_name'].'</a></td>';							
			$topocount++;
			
		}
		$mapViewer .= '</div>';
		$tabs .= '</tr></table>'.$mapViewer;
		
		$lothtml = '<div id="unitViewer">'.$html['html'].'</div>';
		
		$coordxy = $html['coordinate'];
		
		die(json_encode(array("success" => true, "htmldata" => $lothtml, "coordxy" =>$coordxy,  "topo_img" => $tabs)));
		
}

if($funct == 'fetch_elevation' && $page == 'choose_your_home')
{
	
	$elevation = $wpdb->get_results("select * from builder_elevation order by elevationid");
	$html = '<table class="table tbl_elevation">';
	$subcount = 0;
	$open_close = 0;
		
	foreach($elevation as $key => $obj){
		if($subcount == 0){$html .= '<tr><td>'; $open_close = 1;}
		
		$html .= '<div style="width: 33.33%; float: left; padding: 5px;" class="elev_div">';
			$html .= '<img src="'.$obj->image.'" style="width: 100%; height: 200px;"><br />';
			$html .= '<p><strong>'.$obj->title.'</strong></p>';
			$html .= '<p>'.$obj->description.'</p>';
			$html .= '<p><input type="button" onClick="chooseNext(4,'.$obj->elevationid.')" value="Select Elevation" /></p>';
		$html .= '</div>';
		
		$subcount++;
		if($subcount == 3){$html .= '</td></tr>'; $subcount=0; $open_close = 0;}
	}
	if($open_close == 1){$html .= '</td></tr>';}
	$html .= '</table>';

	die(json_encode(array("success" => true, "htmldata" => $html)));
}

if($funct == 'fetch_lotoptions' && $page == 'choose_your_home')
{
	$lotid = $_POST['unitid'];
	$planid = $_POST['planid'];
	$optioncount = array();
	
	$SQL1 = "SELECT optiongroupname FROM builder_phaseplanoptions WHERE phaseplan_id = $planid GROUP BY optiongroupname ";
	$parent = $wpdb->get_results($SQL1);
	
	
	$html = '<table style="width: 76%;" class="lt_table"><tr><td style="width: 70%;" class="lt_td1">';
		$html .= '<table style="width: 100%; border-style: collapse;" class="lt_table2">';
		$n = 0;
	foreach($parent as $key => $val){
		$n++;
		$html .= '<tr class="grp" id="grp-id-' . $n . '"><td style="background-color: #ccc;" class="lt_td2"><strong>'.$val->optiongroupname.'</strong></td></tr>';
			$group = $val->optiongroupname;
			$SQL2 = "SELECT * FROM builder_phaseplanoptions WHERE phaseplan_id = $planid AND optiongroupname = '$group'";
			$child = $wpdb->get_results($SQL2);
			
			$cnt = 0;
			$open_close = 0;
			foreach($child as $kk => $vval){
				
				if(strlen($vval->optionlongdesc) > 0){$longdesc = $vval->optionlongdesc;}else{$longdesc = $vval->optiondesc;}
				if(strlen($vval->imageurl) > 0){$imgurl = $vval->imageurl;}else{ $imgurl = plugins_url('../assets/images/no-img-option.gif',dirname(__FILE__));}
				if($cnt == 0){$html .= '<tr class="cld grp-id-' . $n . '"><td class="lt_td3" style="width: 100%;">'; $open_close = 1;}
				if($cnt == 2){ $mrg = '0px';}else{ $mrg = '5px';}
				$html .= '
						   <div class="lt_div" style=" float: left; padding: 5px; margin-right: '.$mrg.'; border: solid 1px #ccc;">
						   		<img src="'.$imgurl.'" style="width: 100%; height: 200px;" class="lt_img">
						   		<p class="lt_desc">'.$longdesc.'</p>
								<p class="lt_desc_input" style="padding-top: 5px; border-top: solid 1px #ccc;">									
									<input type="checkbox" name="option_'.$vval->id.'" id="option_'.$vval->id.'" value="'.$vval->optiondesc.'" onClick="addThisItem('.$vval->id.')">  Select this item: 
									<select name="qty_'.$vval->id.'" id="qty_'.$vval->id.'" onChange="calculateOption('.$vval->id.')" disabled>
										<option value="0">0</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
									</select> 
								</p>
							</div>';	
				$cnt++;
			 	if($cnt == 3){$html .= '</td></tr>'; $cnt=0; $open_close = 0;}	
			 		
				$optioncount[] = array('id' => $vval->id, 'price' => $vval->unitprice);
				
			}
			
			if($open_close == 1){$html .= '</td></tr>';}
	}
		$html .= '</table>';
	$html .= '</td></tr></table>
			  <div class="lt_td6">
			  <div class="lt_td7">
			  	<div id="lotinfo-div"></div>
			  	<p><strong>Options and Calculation Details:</strong></p>
			  	<table id="optcalc"><tbody></tbody></table>
			  	<div id="info-total"></div>
				
				</div>
				<div class="lt_btn_submit"><input type="button" onClick="chooseNext(5,0)" value="Submit for Review" class="fp_view_btn" /></div>
			  </div>';

	$html .= '';
	$planname = $wpdb->get_results("select * from builder_subdivisionplan where id = $planid");
	$lotinfo = $wpdb->get_results("select a.*,b.name as phase_name from builder_phaselot a left join builder_subdivisionphase b on a.phase_id=b.id where a.id = $lotid ");
	
	$lottpl = '<ul class="lt_table3">';
	$lottpl .= '<li class="lt_rtd">'.$planname[0]->planname.'</li>';
	$lottpl .= '<li class="lt_rtd">'.$lotinfo[0]->phase_name.'</li>';
	$lottpl .= '<li class="lt_rtd">#'.$lotinfo[0]->lotunitnum.'</li>';
	$lottpl .= '<li class="lt_rtd">'.$lotinfo[0]->address1.' '.$lotinfo[0]->city.', '.$lotinfo[0]->state.'</li>';
	$lottpl .= '</ul>';
	
	die(json_encode(array("success" => true, "lotinfo" => $lottpl, "lotprice" => $lotinfo[0]->lotprice,  "htmldata" => $html, "optioncount" => $optioncount)));
}

if($funct == 'fetch_required_option')
{
	$subid = $_POST['subid'];
	$optionid = $_POST['optionid'];
	$planid = $_POST['planid'];
	
	$divinfo = $wpdb->get_results("SELECT b.id FROM builder_subdivision a LEFT JOIN builder_division b ON b.code = a.division_code WHERE a.id = $subid ");
	$divid = $divinfo[0]->id;
	
	$optioninfo = $wpdb->get_results("SELECT * FROM builder_phaseplanoptions WHERE id = $optionid ");
	$masteroptionid = $optioninfo[0]->masteroptionid;
	
	$requireinfo = $wpdb->get_results("SELECT * FROM builder_optionrule WHERE option_master = $masteroptionid AND divisionid = $divid AND option_type = 'Requires'");
	
	
	
	if(count($requireinfo) > 0){
		
		$datainfo = $wpdb->get_results("SELECT * FROM builder_phaseplanoptions WHERE masteroptionid IN (".$requireinfo[0]->option_required.") AND phaseplan_id = $planid GROUP BY masteroptionid");
		$html = '<h4>Requires:</h4>';
		foreach($datainfo as $key => $val){
			if(strlen($val->optionlongdesc) > 0){$longdesc = $val->optionlongdesc;}else{$longdesc = $val->optiondesc;}
			$html .= '<input type="radio" name="selectoption" onClick="addThisItemChild('.$val->id.')" value="'.$val->id.'"> '.$longdesc.'<hr>';
		}
		$html .= '<input type="button" onClick="overlay()" value="Save Changes" class="fp_view" />';
		die(json_encode(array("success" => true, "htmldetail" => $html)));
		
	}else{
		die(json_encode(array("success" => false)));
	}
		
}

if($funct == 'fetch_submitforreview' && $page == 'choose_your_home'){

	@session_start();
	
	$maindata = $_POST['requestdata'];
	$opt = array();
	$optin = array();
	
	foreach($_POST['options'] as $key => $val){
		
		if($val['status'] == 'true'){
			$opt[] = array(
				'id' => $val['id'],
				'status' => $val['status'],
				'count' => $val['count'],
				'price' => $val['price'],
				'desc' => $val['desc']
			);
			
			$optin[] = $val['id'];
		}
		
	}
	
	$wpdb->insert( 
			'builder_choosehome_request', 
			array( 
				'user_email' =>  $_SESSION['useremail'],
				'subdivisionid' => $maindata['global_subdivisionid'], 
				'planid' => $maindata['global_planid'], 
				'unitid' => $maindata['global_unitid'], 
				'elevationid' => $maindata['global_elevationid'], 
				'options' => json_encode($opt), 
				'is_granted' => 0, 
				'date_added' => date('Y-m-d H:i:s')
			)
		);
		
	$html = get_saved_homedesign($_SESSION['useremail']);
	
	#lead settings
	$userinfo = get_user_info($_SESSION['useremail']);
	$subdivinfo = get_subdivision_info($maindata['global_subdivisionid']);
	$lotinfo = get_phaselot_info($maindata['global_unitid']);
	$phaseplaninfo = get_subphase_phaseplan($maindata['global_planid']);
	
	if(count($optin) > 0){
		
		$optioninfo = get_option_codes(implode(', ', $optin));
		$optnote = 'OptionCodes='.$optioninfo;
		
	}else{
		$optnote = 'OptionCodes=';
	}
	
	
	
	$params['buildername'] = 'Sales Simplicity Demo'; //TODO: put your Buildername
	$params['email'] = $_SESSION['useremail'];
	$params['fname'] = $userinfo->firstname;
	$params['lname'] = $userinfo->lastname;
	$params['phone'] = $userinfo->phone;
    $params['notes'] = 'Subdiv='.$subdivinfo->name."\n".
    				   'Lot='.$lotinfo->jobunitnum."\n".
    				   'Phase='.$phaseplaninfo->phasename."\n".
    				   'Plane Name='.$phaseplaninfo->planname."\n".$optnote;
	$params['ip'] = '';
		
	submit_leads($params);
	
	die(json_encode(array("success" => true, "htmldata" => $html)));
}

if($funct == 'delete_home_design' && $page == 'choose_your_home')
{
	@session_start();
	$design = $_POST['designid'];
	$userid = $_SESSION['useremail'];
	
	$wpdb->query("delete from builder_choosehome_request where id = $design ");
	
	$html = get_saved_homedesign($userid);
	
	die(json_encode(array("success" => true, "htmldata" => $html)));
}

if($funct == 'get_pamphlet' && $page == 'choose_your_home')
{
	@session_start();
	$id = $_GET['idx'];
	$userid = $_SESSION['useremail'];
	generate_pamphlet_pdf($id,$userid);
}

if($funct == 'fetch_floorplan' && $page == 'floorplan')
{
	$data = array();
	if(strlen($_POST['subcode']) > 0){$condition = " AND subdivision_code = '".$_POST['subcode']."' ";}else{$condition = "";}
	$sql = "SELECT 
			a.* ,
			(SELECT filename FROM builder_subdivisionattachment WHERE subdivisionplanid = a.id LIMIT 1) AS plan_image
			FROM 
			builder_subdivisionplan a 
			WHERE plan_status = 'active'
			".$condition."
			GROUP BY plannumber
			ORDER BY subdivision_code DESC";
			
	$result = $wpdb->get_results($sql);
	$html = '<table style="width: 100%" class="tbl-floorplan">';

	foreach($result as $key => $obj){
		
		$html .= '<tr><td style="width: 30%;" class="tbl_flr_img">
					  <img src="http://www.salessimplicity.net/Livedemo/attachments/'.$obj->plan_image.'" style="width: 300px;">
				  </td><td class="flr_details_row">			 
						  <h4>'.$obj->planname.'</h4>
						  <table><tr>						  	 
							  <td style="padding-left: 0px;"  class="td-flr_details">
							  <div class="flr_row"><ul><li class="d-left">BEDS:</li><li class="d-right"> '.$obj->bedrooms.'</li></ul></div>
							  <div class="flr_row"><ul><li class="d-left">BATHS:</li><li class="d-right"> '.$obj->baths.'</li></ul></div>
							  <div class="flr_row"><ul><li class="d-left">STORIES:</li><li class="d-right"> '.$obj->stories.'</li></ul></div>
							   <div class="flr_row"><ul><li class="d-left">GARAGE:</li><li class="d-right"> 0</li></ul></div>
							  <div class="flr_row"><ul><li class="d-left">SQFT:</li><li class="d-right"> '.$obj->basesqft.'</li></ul></div>
							  </td>
							 <td class="td-flr_descr"><p>'.$obj->description.'</p></td>
						  </tr></table>
						  <p><a href="'.get_site_url().'/builderux-floor-plan-details/?plan='.$obj->planname.'" class="fp_view">View Details</a></p>
						  
				  </td>
				  
				  </tr>';
	}

	$html .= '</table>';
	
	die(json_encode(array('success' => true, 'plan' => $html)));
}

if($funct == 'fetch_floorplan_detail' && $page == 'floorplan')
{
	$plan_name = $_GET['plan'];
	$data = array();
	$floorplanimg =  array();
	$images = '';
	$atsubdiv = '';
	$atavaillot = '';
	
	$sql = "SELECT 
			a.*,
			attachmenturl 
			FROM builder_phaseplanattachment a 
			LEFT JOIN builder_phaseplan spp ON spp.phase_id = a.phaseplan_id
			LEFT JOIN builder_subdivisionphase sp ON sp.id = spp.phase_id
			LEFT JOIN builder_subdivision s ON s.code = sp.subdivision_code
			LEFT JOIN builder_division sd ON sd.code = s.division_code	
			LEFT JOIN builder_master sm ON sm.builder_code=sd.builder_code
			WHERE phaseplan_id IN (SELECT id  FROM builder_phaseplan WHERE planname = '$plan_name') 
			GROUP BY originalfilename,a.type";
			
	
	$result = $wpdb->get_results($sql);
	
	$plan = $wpdb->get_results("SELECT * FROM builder_subdivisionplan WHERE planname = '$plan_name' LIMIT 1");
	$availsubdiv = $wpdb->get_results("SELECT b.name,b.name AS linkname,b.id AS linkid,a.id AS planid,a.planname AS planname FROM builder_subdivisionplan a LEFT JOIN builder_subdivision b ON a.subdivision_code = b.code WHERE a.planname = '$plan_name' AND show_subdivision='Y'");
	
	$avl = "SELECT 
			a.*,
			s.name AS linkname,
			a.id AS linkid 
			FROM builder_phaselot a
			LEFT JOIN builder_subdivisionphase sp ON sp.id = a.phase_id
			LEFT JOIN builder_subdivision s ON s.code = sp.subdivision_code
			WHERE show_lot_status='Active' AND lotstatus IN ('Spec','Model') 
			AND a.id IN (SELECT lot_id FROM builder_lotplanallowed WHERE masterplanid = ".$plan[0]->masterplanid.")
			AND s.show_subdivision='Y' AND a.show_lot_status='Active' GROUP BY a.phase_id";
	
	
	$availlot = $wpdb->get_results($avl);
	
	if(count($plan) > 0){$description = $plan[0]->description;}else{$description = '';}
	
	$specs = '<table style="width: 100%;" class="mhd_tbl no-nth"> 
				    	<tr><td style="width: 50%;">Bedrooms</td><td style="width: 50%;">'.$plan[0]->bedrooms.'</td></tr>
				    	<tr><td>Bathrooms</td><td>'.$plan[0]->baths.'</td></tr>
				    	<tr><td>Stories</td><td>'.$plan[0]->stories.'</td></tr>
				    	<tr><td>Sqt ft.</td><td>'.$plan[0]->basesqft.'</td></tr>
				    	<tr><td>Price</td><td>$'.$plan[0]->baseprice.'</td></tr>		    	
			  </table>';
			  
	$atsubdiv .= '<table style="width: 100%;">'; 
	foreach($availsubdiv as $key => $val){
		$atsubdiv .= '<tr><td>'.$val->name.'</td></tr>';
	}				    	
	$atsubdiv .= '</table>';
	
	$atavaillot .= '<table style="width: 100%;">'; 
	foreach($availlot as $key => $val){
		$atavaillot .= '<tr><td>'.$val->address1.' '.$val->jobunitnum.' ($'.$val->lotprice.')</td></tr>';
	}				    	
	$atavaillot .= '</table>';

	foreach($result as $key => $obj){
		if(strlen($obj->attachmenturl) > 0 && strlen($obj->filename) > 0){
			$images .= '<div style="background: #ccc; border: solid 5px #ccc; float: left; margin-bottom: 10px; margin-right: 10px;" class="mhd_dvimg"><img src="'.$obj->attachmenturl.'/'.$obj->filename.'" style="width: 350px;"></div>';
		}
	}

	die(
		json_encode(
			array(
				'success' => true, 
				'desc' => $description,
				'images' => $images,
				'specs' => $specs,
				'atsubdiv' => $atsubdiv,
				'atavaillot' => $atavaillot
			)
		)
	);
}

if($funct == 'get_unitlot_byphase')
{
	$html = get_unit_data($_POST['subdivisionid'],$_POST['phaseid']);
	
	die(json_encode(array("success" => true, "unitlot" => $html['html'], "coordxy" => $html['coordinate'])));
}

function get_unit_data($subdivisionid,$phaseid)
{
	global $wpdb; 
	$coord = array();
	$rturn = array();
	
	if($phaseid > 0){
		$subsql = "AND phase_id = $phaseid";
	}else{
		$subsql = " ";
	}
	
	$UNITSQL = "SELECT 
					a.*,
					b.filename
					FROM builder_phaselot a
					LEFT JOIN builder_lotattachment b ON b.lotid = a.id
					WHERE a.phase_id IN (
						SELECT
						builder_subdivisionphase.id
						FROM builder_subdivisionphase
						WHERE builder_subdivisionphase.subdivision_code = (SELECT builder_subdivision.code FROM builder_subdivision WHERE builder_subdivision.id = $subdivisionid)
					)
					AND a.lotstatus = 'Available' ".$subsql." ORDER BY a.id ASC";
					
		$unit = $wpdb->get_results($UNITSQL);
		
		$html = '<table class="wwb_table" style="width: 100%;">';
		$subcount = 0;
		$open_close = 0;
		
		foreach($unit as $key => $obj){
			if($subcount == 0){$html .= '<tr><td class="wwb_table_td" style="width: 100%;">'; $open_close = 1;}
			$html .= '<div class="wwb_div" style="width: 33.33%; float: left; padding: 5px;">';
			
			if(strlen($obj->filename) > 0){
					   $html .= '<img class="wwb_img" src="http://www.salessimplicity.net/Livedemo/attachments/'.$obj->filename.'" style="width: 100%; height: 200px;">';
				}
				   
					   $html .= '<h4 class="wwb_h4">Unit #'.$obj->lotunitnum.'</h4>';
			if(strlen($obj->url) > 0){
					   $html .= '<p><strong><a href="'.$obj->url.'">View Brochure</a></strong></p>';
				}
					   $html .= '<p><strong>Restriction: </strong>'.$obj->restriction.'</p>
					   <p><input type="button" onClick="chooseNext(3,'.$obj->id.')" value="Select This Unit" class="fp_view" /></p>
				  </div>';
				  
			 $subcount++;
			 if($subcount == 3){$html .= '</td></tr>'; $subcount=0; $open_close = 0;}
			 
			 #add for icon coordinate
			 if(!is_null($obj->phasexcoord)){
				 $coord[] = array(
				 	"pointx" =>$obj->phasexcoord,
				 	"pointy" => $obj->phaseycoord,
				 	"unitid" => $obj->id
				 );
			 }
		}
		
		if($open_close == 1){$html .= '</td></tr>';}
		$html .= '</table>';
		
		$rturn['html'] = $html;
		$rturn['coordinate'] = $coord;
		
		return $rturn;	
}

function get_subdivision_topo($subid)
{
	$url = "http://www.salessimplicity.net/livedemo/topofiles/";
	global $wpdb; 

	$topo = $wpdb->get_results("select * from builder_subdivision where id = $subid ");
	
	$url = $url.$topo[0]->topoimagepath;
	
	return $url;
}

function get_subdivision_phasetopo($phaseid)
{
	$url = "http://www.salessimplicity.net/livedemo/topofiles/";
	global $wpdb; 
	$data = array();

	$info = $wpdb->get_results("select * from builder_subdivisionphase where id = $phaseid ");
	
	$data['url'] = $url.$info[0]->topoimagepath;
	$data['phasename'] = $info[0]->name;
	
	return $data;
}


function get_phases_data($subdivisionid)
{
	global $wpdb; 
	$data = array();
	
	$SQL = "SELECT 
					a.*,
					b.filename
					FROM builder_phaselot a
					LEFT JOIN builder_lotattachment b ON b.lotid = a.id
					WHERE a.phase_id IN (
						SELECT
						builder_subdivisionphase.id
						FROM builder_subdivisionphase
						WHERE builder_subdivisionphase.subdivision_code = (SELECT builder_subdivision.code FROM builder_subdivision WHERE builder_subdivision.id = $subdivisionid)
					)
					AND a.lotstatus = 'Available'
					GROUP BY phase_id
					ORDER BY a.id ASC";
					
		$phase = $wpdb->get_results($SQL);
		
		foreach($phase as $key => $val){
			$phasetopo = get_subdivision_phasetopo($val->phase_id);
			$data[] = array(
				'phaseid' => $val->phase_id,
				'phase_name' => $phasetopo['phasename'],
				'topoimage' => $phasetopo['url']
			);
		}			
		
		return $data;	
}

function get_saved_homedesign($useremail)
{
	global $wpdb; 
	$html = '<table class="cs_table">';
	$info = $wpdb->get_results("select * from builder_choosehome_request where user_email = '$useremail' order by id desc");
	
	foreach($info as $key => $val){
				
		$subdivinfo = get_subdivision_info($val->subdivisionid);
		$phaselotinfo = get_phaselot_info($val->unitid);
		$subphaseplan = get_subphase_phaseplan($val->planid);
		$html .= '<tr>
					<td class="cs_td"><img class="cs_img" src="'.$subdivinfo->custportallogourl.'" style="width: 623px; height: 415px;"></td>
					<td> 
						<table class="cs_table2">
							<tr><td class="cs_td"><h4 class="mvin_h4">'.$subdivinfo->name.'</h4></td></tr>
							<tr><td class="cs_td">
								
								<table class="tbl_data">
								<tr>
									<td><strong>Email:</strong><br /> '.$subdivinfo->subleadsemail.'</td>
									<td><strong>Fax:</strong><br /> '.$subdivinfo->fax.'</td>
								</tr>
								<tr>
									<td><strong>Address:</strong><br /> '.$subdivinfo->county.' '.$phaselotinfo->address1.'</td>
									<td><strong>Plan Name:</strong><br /> '.$subphaseplan->planname.' '.$subphaseplan->phasename.' Lot#'.$phaselotinfo->lotunitnum.' Plan#'.$subphaseplan->phaseplanid.'</td>
								</tr>
								</table>
							</td></tr>
							<tr><td class="cs_td">
								<h4 class="cs_h4" style="color: #5ac763;">Description</h4>
								<p>'.$subdivinfo->marketingdescription.'</p>
								<p class="data_btn">
									<input type="button" value="Delete" onClick="deleteHomeDesign('.$val->id.')" class="fp_view"> 
									<input type="button" value="Edit Selections" class="fp_view"> 
									<input type="button" value="Generate Pamphlet" onClick="getPamphlet('.$val->id.')" class="fp_view">
								</p>
							</td></tr>
						</table>
					</td>
				  </tr>';
				  
	}
	
	$html .= '</table>';
	
	return $html;
}

function get_subdivision_info($subid)
{
	global $wpdb; 
	$info = $wpdb->get_results("select * from builder_subdivision where id = $subid ");
	
	return $info[0];
}

function get_phaselot_info($unitid)
{
	global $wpdb;
	$info = $wpdb->get_results("SELECT * FROM builder_phaselot WHERE id = $unitid ");	
	
	return $info[0];
}

function get_subphase_phaseplan($planid)
{
	global $wpdb;
	
	$info = $wpdb->get_results("SELECT a.planname,b.name AS phasename, a.phaseplanid, a.baseprice FROM builder_phaseplan a LEFT JOIN builder_subdivisionphase b ON a.phase_id = b.id WHERE a.id = $planid ");
	
	return $info[0];
}

function get_elevation_details($elevid)
{
	global $wpdb;
	
	$info = $wpdb->get_results("SELECT * FROM builder_elevation WHERE elevationid = $elevid ");
	
	return $info[0];
}

function get_option_image($optid)
{
	global $wpdb;
	
	$info = $wpdb->get_results("SELECT imageurl FROM builder_phaseplanoptions WHERE id = $optid ");
	
	return $info[0]->imageurl;
}

function get_subphase_lot($unitid)
{
	global $wpdb;
	
	$info = $wpdb->get_results("SELECT * FROM builder_phaselot WHERE id = $unitid ");
	
	return $info[0];
}

function get_user_info($email)
{
	global $wpdb;
	
	$info = $wpdb->get_results("SELECT * FROM builder_user WHERE email = '$email' ");
	
	return $info[0];
}

function get_option_codes($options)
{
	global $wpdb;
	$rs = array();
	$info = $wpdb->get_results("SELECT optionCode FROM `builder_phaseplanoptions` WHERE id IN($options)");
	
	foreach($info as $key => $val){
		$rs[] = $val->optionCode;
	}

	return implode(', ', $rs);
}

function get_lead_settings()
{
	global $wpdb;
	
	$info = $wpdb->get_results("SELECT * FROM builder_lead_settings limit 1 ");
	
	return $info[0];
}

function submit_leads($params)
{
	$leadinfo = get_lead_settings();
	
	$client = new SoapClient($leadinfo->api_source);
	$guid = $leadinfo->guid;
	
	$lead = array(
		'BuilderName' => $params['buildername'], //TODO: put your Buildername
		'Email'	=> $params['email'],
		'FirstName' => $params['fname'],
		'LastName' => $params['lname'],
		'Phone' => $params['phone'],
		'Demos' => array('comment='),
		'EmailValidation' => 'Yes',
		'Note' => $params['notes'],
		'IPAddress' => $params['ip']
	);
	
	$lead = (object)$lead;
	
	$result = $client->SubmitLead(array('sGUID' => $guid,'Contact' => $lead));
}

function generate_pamphlet_pdf($rowid,$userid)
{
	require_once(dirname(__FILE__).'/../../assets/html2pdf/vendor/autoload.php');

	global $wpdb;
	set_time_limit(0);
	
	
	$info = $wpdb->get_results("select * from builder_choosehome_request where user_email = '$userid' and id = $rowid ");
	$subdivinfo = get_subdivision_info($info[0]->subdivisionid);
	$subphaseplan = get_subphase_phaseplan($info[0]->planid);
	$subphaselot = get_subphase_lot($info[0]->unitid);
	$elevinfo = get_elevation_details($info[0]->elevationid);
	
	$imgsql = "SELECT 
			attachmenturl, a.filename
			FROM builder_phaseplanattachment a 
			LEFT JOIN builder_phaseplan spp ON spp.phase_id = a.phaseplan_id
			LEFT JOIN builder_subdivisionphase sp ON sp.id = spp.phase_id
			LEFT JOIN builder_subdivision s ON s.code = sp.subdivision_code
			LEFT JOIN builder_division sd ON sd.code = s.division_code	
			LEFT JOIN builder_master sm ON sm.builder_code=sd.builder_code
			WHERE phaseplan_id IN (SELECT id  FROM builder_phaseplan WHERE planname = '".$subphaseplan->planname."') 
			GROUP BY originalfilename,a.type";
			
	$imginfo = $wpdb->get_results($imgsql);
	
	# begin content here
	
	$content = '<p style="font-size: 13px;">
					<strong>You can contact us anytime at</strong> <br />
					<strong>'.$subdivinfo->subleadsemail.'</strong><br />
					<strong>'.$subdivinfo->fax.'</strong>
				</p><hr>';
	
	if(@getimagesize($imgsrc) && strlen($subdivinfo->custportallogourl) > 0){			
		$content .= '<img src="'.$subdivinfo->custportallogourl.'" style="width: 600px;">';
	}
	
	$content .= '<p style="font-size: 13px;>'.$subdivinfo->marketingdescription.'</p><hr>
				<p style="font-size: 13px;><strong>'.$subphaseplan->planname.'</strong> ($'.$subphaseplan->baseprice.')</p>';
				
	if(count($imginfo) > 0){
		$xcount = 0;
		foreach($imginfo as $key => $val){
			$xcount++;
			$imgsrc = "http://www.salessimplicity.net/Livedemo/attachments/".$val->filename;
			if(@getimagesize($imgsrc) && strlen($val->filename) > 0){
				$optimgx = str_replace(" ","%20",$imgsrc);
				$content .= '<img src="'.$optimgx.'" style="width: 200px; margin-right: 10px;">';
			}

			if($xcount == 3){
				$xcount = 0;
				$content .= '<br />';
			}
		}
	}
	
	$content .= '<p style="font-size: 17px; padding-bottom: 10px; border-bottom: solid 2px #000000;"><strong>Option Selected</strong></p><hr>';
	$content .= '<table style="width: 900px;">
					<tr><td><img src="'.$elevinfo->image.'" style="width: 200px;"></td>
					<td style="text-align: center; padding: 10px; width: 235px;"><strong>'.$elevinfo->title.'</strong></td>
					<td style="text-align: center; padding: 10px; width: 235px;"> $'.$elevinfo->price.'</td></tr>
					<tr><td colspan="3"><hr></td></tr>
				';
	
	$optdata = json_decode($info[0]->options);
	$totalopt = 0;
	if(count($optdata) > 0){
		foreach($optdata as $key => $val){
			$totalopt = $totalopt + $val->price;
			$optimg = get_option_image($val->id);
			
			if(@getimagesize($optimg) && strlen($optimg) > 0){
				$optimg = str_replace(" ","%20",$optimg);
				$content .= '<tr><td><img src="'.$optimg.'" style="width: 200px;"></td>';
				
			}else{
				$content .= '<tr><td><strong>No Image Available</strong></td>';
			}
			

			$content .= '
						<td style="text-align: center; padding: 10px;"><strong>'.$val->desc.'</strong></td>
						<td style="text-align: center; padding: 10px;"> $'.$val->price.'</td></tr>';
		}
	}
	
	$content .= '<tr><td colspan="3"><hr></td></tr></table>';
	
	$content .= '<p style="font-size: 17px; border-bottom: solid 2px #000000;"><strong>General Summary</strong></p><hr>';
	
	$content .= '<table>';
	$content .= '<tr><td><strong>Plan Price:</strong></td><td>$'.$subphaseplan->baseprice.'</td></tr>';
	$content .= '<tr><td><strong>Lot Premium:</strong></td><td>$'.$subphaselot->premium.'</td></tr>';
	$content .= '<tr><td><strong>Options Total:</strong></td><td>$'.$totalopt.'</td></tr>';
	$content .= '<tr><td><strong>General Total:</strong></td><td>$'.($subphaseplan->baseprice + $subphaselot->premium + $elevinfo->price + $totalopt).'</td></tr>';
	$content .= '</table>';
	
	#end content

	try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'en');

        $html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($content);
        $html2pdf->Output('exemple.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
    
}

?>