<!-- Inscription form -->
<form draggable="false" action="" method="post" id="inscription_form">
    <table class="form_table">
        <tbody>
            <tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_1; ?> :</label></td>
                <td><input type="text" required="" autofocus="" name="username"></td>
            </tr>
            <tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_10; ?> :</label></td>
                <td><input type="email" required="" name="mail"></td>
            </tr>
            <tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_2; ?> :</label></td>
                <td><input type="password" required="" name="password"></td>
            </tr>
            <tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_11; ?> :</label></td>
                <td><input type="password" required="" name="password_verif"></td>
            </tr>
            <tr>
                <td class="ucfirst">
                    <a href="<?php echo $this->captchaRefreshUrl; ?>" class="cursor refresh-captcha">
                        <img class="cursor captach-image" src="<?php echo $this->captchaImageUrl; ?>" width="279" height="60" alt="<?php echo $this->langs->frontoffice_19; ?>" title="<?php echo $this->langs->frontoffice_12; ?>"/>
                    </a>
                    <a href="<?php echo $this->captchaAudioUrl; ?>" class="cursor play-captcha">
                        <img class="cursor" src="<?php echo $this->getUrlAsset('img'); ?>haut-parleur.png" width="60px" height="60px" alt="<?php echo $this->langs->frontoffice_20; ?>" title="<?php echo $this->langs->frontoffice_13; ?>"/>
                    </a>
                </td>
                <td><input type="text" id="captcha" name="captcha" required="required"></td>
            </tr>
            <tr>
                <td><input type="hidden" value="<?php echo $this->token; ?>" id="token" name="token"></td>
                <td><input type="submit" value="<?php echo $this->langs->frontoffice_14; ?>" class="button"></td>
            </tr>
        </tbody>
    </table>
</form>