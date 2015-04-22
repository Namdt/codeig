<?= $this->session->flashdata('error'); ?>
<div class="box5">
	<h1>Admin Login</h1>	
	<p>Please enter your email and password to continue</p>
	<form action="<?=base_url();?>users/do_login" method="post">
	<ul class="list1">
        <li><label>Email</label><?php echo form_input(array('name' => 'email', 'maxlength' => 100)); ?></li>
        <li><label>Password</label><?php echo form_password(array('name' => 'password')); ?></li>          
    </ul>
	<div class="regSubmit">
       <input type="submit" value="Continue" class="button button-rounded button-action">
    </div>
	</form>
</div>
<?= md5('NamThoCompany'.SALT); ?>