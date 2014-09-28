<!-- bot trap -->
<a class="hide" href="?badbottrap"></a>

<!-- Header -->
<header id="header"> 
    <div id="logo">
        <a id="logo_link" href=""><img width="800" height="150" alt="Logo" src="<?php echo $this->getUrlAsset('img'); ?>banner.png" id="logo_img"></a>
    </div>
</header>

<!-- Connection bar -->
<div id="connect_bar">
    <?php if (!$this->isConnected) { ?>
        <?php include $this->getPath() . 'forms' . DS . 'login.tpl.php'; ?>
    <?php } else { ?>
        <table id="connect_bar_table">
            <tbody>
                <tr>
                    <td width="35px">
                        <a href="<?php echo $this->getUrl('language', array($this->langAvaible)); ?>" class="updateLanguage" id="<?php echo $this->langAvaible; ?>"><img width="25px" height="25px" alt="En" src="<?php echo $this->getUrlAsset('img'); ?><?php echo $this->langAvaible; ?>.png"></a>
                    </td>
                    <td class="ucfirst"><span class="username">dread</span></td>
                    <td class="ucfirst"><a href=""><?php echo $this->langs->frontoffice_21; ?></a></td>
                    <td class="ucfirst"><a href="" class="logout_link"><?php echo $this->langs->frontoffice_22; ?></a></td>
                </tr>
            </tbody></table>
    <?php } ?>
</div>

<!-- Navigation bar -->
<nav id="nav">
    <table id="nav_table">
        <tbody><tr>
                <td><a href="<?php echo $this->getUrl('index'); ?>" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_4; ?></span></div></a></td>
                <td><a href="<?php echo $this->getUrl('story'); ?>" class="nav_link"><div class="button nav_button part_edit ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_5; ?></span></div></a></td>
                <td><a href="<?php echo $this->getUrl('page', array('faq')); ?>" class="nav_link"><div class="button nav_button capitalize"><span class="nav_text"><?php echo $this->langs->frontoffice_6; ?></span></div></a></td>
                <td><a href="<?php echo $this->getUrl('page', array('partners')); ?>" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_7; ?></span></div></a></td>
                <td><a href="" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_8; ?></span></div></a></td>
                <td><a href="<?php echo $this->getUrl('page', array('contact')); ?>" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_9; ?></span></div></a></td>
            </tr>
        </tbody>
    </table>
</nav>

<!-- Social media-->
<div class="addthis_sharing_toolbox"></div>