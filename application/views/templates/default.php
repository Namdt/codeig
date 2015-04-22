<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        <?php echo $title_for_layout; ?> | <?php echo $site_setting['site_name']; ?>
    </title>
    <meta name="description" content="<?=$title_for_layout?> | <?=$site_setting['site_name']?> | <?=$site_setting['site_description']?>"/>
    <meta name="keywords" content="<?php echo $site_setting['site_keywords']; ?>"/>
    <meta property="og:image" content="<?=FULL_BASE_URL.ROOT?>img/og-image.png" />
    <script>
        var baseUrl = '<?=base_url()?>';
    </script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <?php
        echo meta('icon');
		echo link_tag('asset/css/bootstrap.min.css');
		echo link_tag('asset/css/bootstrap-responsive.min.css');

    ?>
</head>
<body>
<?php echo $this->element('misc/fb_include'); ?>

<div id="header">
    <div class="wrapper">
        <?php echo $this->element('misc/logo'); ?>
        <?php echo $this->element('userbox'); ?>
        <?php echo $this->element('main_menu'); ?>
    </div>  
</div>      
<div class="wrapper">   
    <?=html_entity_decode( $moo_setting['header_code'] )?>
    <?php echo $this->element('hooks', array('position' => 'global_top') ); ?>
    <div id="content">      
        <?php echo $this->element('right_column'); ?>           
        <?php echo $this->element('left_column'); ?>
    </div>
    <?php echo $this->element('hooks', array('position' => 'global_bottom') ); ?>
    <?php echo $this->element('footer'); ?> 
</div> 

<?php echo $this->element('sql_dump'); ?>
<?php echo html_entity_decode( $moo_setting['analytics_code'] )?>
</body>
</html>
