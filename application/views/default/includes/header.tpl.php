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
    <!-- Connecting form -->
    <form draggable="false" action="" method="post" id="connect_bar_form">
        <table id="connect_bar_table">
            <tbody>
                <tr>
                    <td width="35px">
                        <a href="<?php echo $this->getUrl('language', array($this->langAvaible)); ?>" class="updateLanguage" id="<?php echo $this->langAvaible; ?>"><img width="25px" height="25px" alt="En" src="<?php echo $this->getUrlAsset('img'); ?><?php echo $this->langAvaible; ?>.png"></a>
                    </td>
                    <td class="ucfirst">
                        <label class="form_label"><?php echo $this->langs->frontoffice_1; ?> : </label><input type="text" required="" name="username">
                    </td>
                    <td class="ucfirst">
                        <label class="form_label"><?php echo $this->langs->frontoffice_2; ?> : </label><input type="password" required="" name="password">
                    </td>
                    <td>
                        <input type="submit" value="<?php echo $this->langs->frontoffice_3; ?>" id="connect_button" class="button">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

<!-- Navigation bar -->
<nav id="nav">
    <table id="nav_table">
        <tbody><tr>
                <td><a href="" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_4; ?></span></div></a></td>
                <td><a href="" class="nav_link"><div class="button nav_button part_edit ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_5; ?></span></div></a></td>
                <td><a href="" class="nav_link"><div class="button nav_button capitalize"><span class="nav_text"><?php echo $this->langs->frontoffice_6; ?></span></div></a></td>
                <td><a href="" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_7; ?></span></div></a></td>
                <td><a href="" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_8; ?></span></div></a></td>
                <td><a href="" class="nav_link"><div class="button nav_button ucfirst"><span class="nav_text"><?php echo $this->langs->frontoffice_9; ?></span></div></a></td>
            </tr>
        </tbody>
    </table>
</nav>

<!-- Social media-->
<div class="addthis_sharing_toolbox"></div>