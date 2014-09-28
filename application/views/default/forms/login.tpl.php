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