<form draggable="false" action="<?php echo $this->getUrl('contact'); ?>" method="post" id="contacts_form">
    <div id="contacts_form_error_box" class="error_box hide ucfirst"></div>
    <div id="contacts_form_success_box" class="message_box hide"></div>
    <table class="form_table contacts_table">
        <tbody><tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_15; ?> :</label></td>
                <td><input type="text" required="" autofocus="" name="contacts_form_name" id="contacts_form_name"></td>
            </tr>
            <tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_16; ?> :</label></td>
                <td><input type="email" required="" name=contacts_form_"mail" id="contacts_form_mail"></td>
            </tr>
            <tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_17; ?> :</label></td>
                <td><input type="text" required="" name="contacts_form_subject" id="contacts_form_subject"></td>
            </tr>
            <tr>
                <td class="ucfirst"><label class="form_label"><?php echo $this->langs->frontoffice_18; ?> :</label></td><td></td>
            </tr>
            <tr>
                <td colspan="2"><textarea required="" maxlength="200000" name="contacts_form_message" id="contacts_form_message" cols="80" rows="6"></textarea></td>
            </tr>
            <tr>
                <td>
                    <a href="<?php echo $this->captchaRefreshUrl; ?>" class="cursor refresh-captcha">
                        <img class="cursor captach-image" src="<?php echo $this->captchaImageUrl; ?>" width="279" height="60" alt="<?php echo $this->langs->frontoffice_19; ?>" title="<?php echo $this->langs->frontoffice_12; ?>"/>
                    </a>
                    <a href="<?php echo $this->captchaAudioUrl; ?>" class="cursor play-captcha">
                        <img class="cursor" src="<?php echo $this->getUrlAsset('img'); ?>haut-parleur.png" width="60px" height="60px" alt="<?php echo $this->langs->frontoffice_20; ?>" title="<?php echo $this->langs->frontoffice_13; ?>"/>
                    </a>
                </td>
                <td><input type="text" required="" name="contacts_form_captcha" id="contacts_form_captcha"></td>
            </tr>
            <tr>
                <td><input type="hidden" value="<?php echo $this->token; ?>" id="contacts_form_token" name="contacts_form_token"></td>
                <td><input type="submit" value="Envoyer" class="button"></td>
            </tr>
        </tbody></table>
</form>