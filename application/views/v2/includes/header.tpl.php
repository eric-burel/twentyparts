<!-- bot trap -->
<a class="hide" href="?badbottrap"></a>
<header>
    <div class="navbar background-red navbar-default navbar-fixed-top pull-left" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="<?php echo $this->getUrl('language', array($this->langAvaible)); ?>" class="updateLanguage margin-left-10" id="<?php echo $this->langAvaible; ?>"><img width="50" height="50" alt="En" src="<?php echo $this->getUrlAsset('img'); ?><?php echo $this->langAvaible; ?>.png"></a>
            <a href="" class="margin-left-10 margin-right-10"><img src="http://placehold.it/250x50" alt="" title=""></a>
        </div>
        <div class="navbar-collapse collapse margin-right-10">
            <ul class="nav navbar-nav">
                <li class="active"><a href="<?php echo $this->getUrl('index'); ?>"><?php echo $this->langs->frontoffice_4; ?></a></li>
                <li><a href="<?php echo $this->getUrl('story'); ?>"><?php echo $this->langs->frontoffice_5; ?></a></li>
                <li><a href="<?php echo $this->getUrl('page', array('faq')); ?>"><?php echo $this->langs->frontoffice_6; ?></a></li>
                <li><a href="<?php echo $this->getUrl('page', array('contact')); ?>"><?php echo $this->langs->frontoffice_9; ?></a></li>
            </ul>
            <?php if (!$this->isConnected) { ?>
                <?php include $this->getPath() . 'forms' . DS . 'login.tpl.php'; ?>
            <?php } else { ?>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="" alt="" title=""><span class="glyphicon bigger glyphicon glyphicon-user"></span></a></li>
                    <li><a href="" alt="<?php echo $this->langs->frontoffice_6; ?>" title="<?php echo $this->langs->frontoffice_21; ?>"><span class="glyphicon bigger glyphicon-wrench"></span></a></li>
                    <li><a href="" alt="<?php echo $this->langs->frontoffice_6; ?>" title="<?php echo $this->langs->frontoffice_22; ?>"><span class="glyphicon bigger glyphicon-off"></span></a></li>
                </ul>
            <?php } ?>

        </div><!--/.navbar-collapse -->
    </div>
</header>