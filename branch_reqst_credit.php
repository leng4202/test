<?php
header ('Content-type: text/html; charset=utf-8'); 
class branch_request_credit extends Gm_Controller {

	public function __construct() {
		parent::__construct();
		if(!$this->session->userdata('user')){
			redirect('index.php/gm_rv_check/login');
		}
	}
	
	public function index(){
		$wh = '';
		
		if(!empty($_POST)) {
		$chk = $this->input->post('pro');
			if($chk != 0){	
				$wh .= " and p.p_id = $chk";	
			}
		//exit;
		}	
		
		$part_1 = $this->session->userdata('part_1');
		$part_2 = $this->session->userdata('part_2');
		$part_3 = $this->session->userdata('part_3');
		$part_4 = $this->session->userdata('part_4');
		$part_5 = $this->session->userdata('part_5');
		$part_6 = $this->session->userdata('part_6');
		$part_7 = $this->session->userdata('part_7');
		
		$part_IN_ = '';
				
		/*if($part_1 == 'T'){
			$part_IN_ .= "'NE'";
		}
		if($part_2 == 'T'){
			if($part_IN_ != '') $part_IN_ .= ',';
			$part_IN_ .= "'N'";
		}
		if($part_3 == 'T'){
			if($part_IN_ != '') $part_IN_ .= ',';
			$part_IN_ .= "'M'";
		}
		if($part_4 == 'T'){
			if($part_IN_ != '') $part_IN_ .= ',';
			$part_IN_ .= "'S'";
		}
		if($part_5 == 'T'){
			if($part_IN_ != '') $part_IN_ .= ',';
			$part_IN_ .= "'E'";
		}
		if($part_6 == 'T'){
			if($part_IN_ != '') $part_IN_ .= ',';
			$part_IN_ .= "'C'";
		}
		if($part_7 == 'T'){
			if($part_IN_ != '') $part_IN_ .= ',';
			$part_IN_ .= "'L'";
		}*/
		
		if(partIN_compass()) $part_IN_ = partIN_compass();
		
		if($this->session->userdata('compass_point') != ''){
			//$wh .= " and s.compass_point = '".$this->session->userdata('compass_point')." ' ";
		}
		
		if(strstr($this->session->userdata('user'),'dit')){
			if($this->session->userdata('user_login') == 'G'){
				$wh .= " and dm_level_pass = 'T' ";
			}else if($this->session->userdata('user_login') == 'B') {
				$wh .= " and gm_level_pass = 'T' ";
			}
			
		}
		
		
		
		if($part_IN_ != ''){
			$wh .= " and s.compass_point IN ($part_IN_)";
		}
		//echo 	$this->session->userdata('compass_point');
		$sql  = $this->db->query("select b.* , count(branch_cuscode) as cou ,g.GEO_NAME , p.p_name, p.p_id , sum(request_amount) as sum_amont   from voucher_branch_limit .branch_request_credit b 
		left join  advice_co_th.system_display s on s.cuscode = b.branch_cuscode left join advice_co_th.province_system p on p.p_id = s.province_id 
		left join advice_co_th.geography g on s.compass_point = g.compass_point 
		where approve_status <> 'T' ".  $wh ."
		group by branch_cuscode 	
		");
		
		$sql_province = $this->db->query("select b.* , count(branch_cuscode) as cou ,g.GEO_NAME , p.p_name, p.p_id , sum(request_amount) as sum_amont   from voucher_branch_limit .branch_request_credit b 
		left join  advice_co_th.system_display s on s.cuscode = b.branch_cuscode left join advice_co_th.province_system p on p.p_id = s.province_id 
		left join advice_co_th.geography g on s.compass_point = g.compass_point 
		where approve_status <> 'T' ". $wh."
		group by branch_cuscode 	");
		
		 $sql_geo  = $this->db->query("select  GEO_NAME,compass_point  from  advice_co_th.geography where compass_point <> '' ");
		$ress = $sql->row();
		$data['province'] = $sql_province->result();
		$data['geo'] = $sql_geo->result();
	
		$data['res'] = $sql->result();
		

			
			$this->template->build('branch_list',$data);
		}
	
	public function request_list($cuscode = NULL){

		
		
		$data['cuscode'] = $cuscode ;
		$when = $chk = $wh = '';
			if(!empty($_POST)) {
		$chk = $this->input->post('choi');
			if($chk != '0'){
				if($chk != 'W')	{
				$wh .= " and approve_pass = '$chk'";	
				}else{
				$wh .= " and approve_status <> 'T'";	
				}
			}	
		//	echo $chk;
		//exit;
		
		}	
		if(strstr($this->session->userdata('user'),'dit')){
			if($this->session->userdata('user_login') == 'G'){
				$when = "WHEN gm_level_pass = 'T' THEN 3"; 
			}else if($this->session->userdata('user_login') == 'U') {
				$when = "WHEN dm_level_pass = 'T' THEN 3"; 
			}
			
		}
		 
		
		$data['choi'] = $chk ;	 
		
		$sql  = $this->db->query("select * ,
		  (
    CASE 
        WHEN approve_pass = 'T' THEN 1
        WHEN approve_pass = 'F' THEN 2
        ".$when."
        ELSE 0
    END) AS total 
		
		   from voucher_branch_limit .branch_request_credit  
		where branch_cuscode = '".$cuscode."' $wh order by total asc ");

		$data['res'] = $sql->result();
		

		 $sql  = $this->db->query("select  p_name  from  advice_co_th.system_display  s
		inner join  advice_co_th.province_system p on p.p_id	 = s.province_id 
		where cuscode = '".$data['res'][0]->branch_cuscode."' ");
		$ress = $sql->row();
		$data['pro']  = '';
		
		if($sql->num_rows() > 0){
		$data['pro'] = iconv("tis-620", "utf-8", $ress->p_name );
		}
		
		$this->template->build('request_list',$data);
		}
	
	public function request($id = null)
	{
		$data['id'] = $id ;
		
		$sql  = $this->db->query("select b.*   from voucher_branch_limit .branch_request_credit b 
		where request_id = $id ");
		$data['res'] = $sql->result();
		

	 	 $sql  = $this->db->query("select  p_name  from  advice_co_th.system_display  s
		inner join  advice_co_th.province_system p on p.p_id	 = s.province_id 
		where cuscode = '".$data['res'][0]->branch_cuscode."' ");
		$ress = $sql->row();
		
		
		
		$data['pro']  = '';
		
		if($sql->num_rows() > 0){
		$data['pro'] = iconv("tis-620", "utf-8", $ress->p_name );
		//exit;
		}
		
		$sql  = $this->db->query("select *   from voucher_branch_limit .branch_request_stm  
		where request_id = $id ");
		$data['stm'] = $sql->result();
		
		
		$this->template->build('request',$data);
	}
	public function chk_data(){
		//return 'test';
		$id = $this->input->post('rid');
		$status = "F";
		//echo $id;
	/*	echo "select b.*   from voucher_branch_limit .branch_request_credit b 
		where request_id = $id and approve_status = 'T' ";	*/
		//exit;
		
		$sql  = $this->db->query("select b.*   from voucher_branch_limit .branch_request_credit b 
		where request_id = '$id' and approve_status = 'T'  ");
		if($sql->num_rows() > 0){
			$status = "T";
		}
		echo $status;
		exit;
		
	}
	
	public function add(){
		$chk = $this->input->post('chk');
		$amount_new = $this->input->post('amount_new');
		
		$erem =  iconv("utf-8", "tis-620", $this->input->post('erem') ); 
		$id = $this->input->post('id');
		$user_id =	$this->session->userdata('id');
		$branch_cuscode =  $this->input->post('branch_cuscode'); 
		$cuscode = $this->input->post('cuscode'); 
		//echo $this->input->ip_address();
		 $status = 'T';
		if($chk == '0'){
			$status = 'F';
			$amount_new = 0;
			$expire = '0000-00-00';
		}else{
			$expire = $this->input->post('expire');
		}
		
		/*echo $expire;
		exit;*/
		
		/*echo $chk;
		exit;*/

			$data = array(
			"approve_status" => "T",
			"approve_date" => date("Y-m-d"),
			"approve_time" => date("Y-m-d H:i:s"),
			"approve_by" => $user_id,
			"approve_machine" => $this->input->ip_address(),
			"approve_pass" => $status,
			"approve_amount" => $amount_new,
			"approve_rem" => $erem	,
			"credit_expire" => 	$expire
			);
			
			$this->db->where('request_id', $id);
		 	$this->db->update('voucher_branch_limit .branch_request_credit', $data);
	 	
		if($chk != '0'){
		 	 $this->db->where('branch_cuscode', $branch_cuscode); 
		 	 $this->db->where('cuscode', $cuscode); 
			 $query = $this->db->get('voucher_branch_limit.branch_config_credit');
		 
		 $data1 = array(
		"credit_extra" => $amount_new,
		"credit_expire" => '0000-00-00',
		"last_config" => date("Y-m-d H:i:s"),
		"approve_by" => $user_id,
		"approve_machine" => $this->input->ip_address(),
		"approve_date" => date("Y-m-d"),
		"credit_expire" => $expire
		);
		 
		 if($query->num_rows() == 0){
			$data2 = array(
			"branch_cuscode" => $branch_cuscode,
			"cuscode" => $cuscode,
			);
		$data1 = 	array_merge($data1,$data2);		
		$this ->db->insert('voucher_branch_limit.branch_config_credit', $data1);	
		$old = 0;
		}else{
		$res = $query->row();
		$old = $res->credit_extra;
		
		$this->db->where('branch_cuscode', $branch_cuscode);	
		$this->db->where('cuscode', $cuscode);
	 	$this->db->update('voucher_branch_limit .branch_config_credit', $data1);
		}
		
		 $data2 = array(
		 "branch_cuscode" => $branch_cuscode,
		"cuscode" => $cuscode,
		"last_credit_extra" => $old,
		"new_credit_extra" => $amount_new,
		"approve_by" => $user_id,
		"approve_machine" => $this->input->ip_address(),
		"approve_date" => date("Y-m-d"),
		);
		
		$this ->db->insert('voucher_branch_limit.branch_config_credit_log ', $data2);	
		}
		
		//echo "";
		redirect('index.php/branch_request_credit');
		//echo "<pre>"; print_r($data1);
	}
}