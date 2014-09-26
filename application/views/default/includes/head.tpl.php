<head>
    <title><?php echo $this->langs->site_name; ?><?php if ($this->title) echo ' - ' . $this->title; ?></title>
    <meta charset="<?php echo $this->getCharset(); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->getCharset(); ?>" />
    <meta http-equiv="Expires" content="24Oct 2018 23:59:59 GMT">
    <meta http-equiv="Cache-Control" content="public;max-age=315360000" />
    <?php if (defined('GOOGLE_VERIFICATION')) { ?>
        <meta name="google-site-verification" content="<?php echo GOOGLE_VERIFICATION; ?>" />
    <?php } ?>
    <meta name="Author" content="<?php echo ADMIN_NAME; ?>" />
    <meta name="Description" content="<?php if ($this->desc) echo $this->desc;else echo $this->langs->site_desc; ?>" />
    <meta name="Keywords" content="<?php if ($this->keywords) echo $this->keywords;else echo $this->langs->site_keywords; ?>" />
    <meta name="Robots" content="index,follow" />
    <link rel="icon" href="<?php echo $this->getUrlAsset('img'); ?>favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $this->getUrlAsset('img'); ?>favicon.png" type="image/x-icon" />
    <style media="screen" type="text/css"><?php echo $this->getCss(); ?></style>
    <!--[if lt IE 7]>
            <div class='aligncenter'><a href="http://www.microsoft.com/windows/internet-explorer/default.aspx?ocid=ie6_countdown_bannercode"><img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg"border="0"></a></div>  
    <![endif]-->
    <!--[if lt IE 9]>
            <script src="<?php echo $this->getUrlAsset('js'); ?>no-autoload/html5.js"></script>
            <script scr="<?php echo $this->getUrlAsset('js'); ?>no-autoload/respond.js"></script>
    <![endif]-->
    <!--[if IE]>
            <link rel="stylesheet" href="<?php echo $this->getUrlAsset('css'); ?>no-autoload/ie.css"> 
    <!--[endif]-->
    <?php if (defined('GOOGLE_UA')) { ?>
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?php echo GOOGLE_UA; ?>']);
        _gaq.push(['_trackPageview']);
        (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>
    <?php } ?>
</head>