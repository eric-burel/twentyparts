<!-- bot trap -->
<a class="hide" href="?badbottrap"></a>
<header>
    <div id="header-logo">
        <img src="http://placehold.it/200x50">
    </div>
    <nav class="navbar navbar-static-top" role="navigation">
        <ul>
            <li><a href="<?php echo $this->getUrl('index'); ?>" class="active"><?php echo $this->langs->frontoffice_4; ?></a></li>
            <li><a href="<?php echo $this->getUrl('story'); ?>"><?php echo $this->langs->frontoffice_5; ?></a></li>
            <li><a href="<?php echo $this->getUrl('page', array('partners')); ?>"><?php echo $this->langs->frontoffice_7; ?></a></li>
            <li><a href="<?php echo $this->getUrl('page', array('faq')); ?>"><?php echo $this->langs->frontoffice_6; ?></a></li>
        </ul>
    </nav>
</header>