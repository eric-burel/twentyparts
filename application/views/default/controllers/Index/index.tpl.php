<!DOCTYPE html>
<html lang="<?php echo $this->lang; ?>">
    <?php include $this->getPath() . 'includes' . DS . 'head.tpl.php'; ?>
    <body>
        <div class="content">
            <?php include $this->getPath() . 'includes' . DS . 'header.tpl.php'; ?>
            <article class="introduction">
                <!-- Messages -->
                <h2>Une nouvelle forme de créativité.</h2>
                <p>Avec TwentyParts.com, découvrez une infinité d'histoires créatives. Le concept est très simple : chaque histoire est divisée en 20 parties, et chaque partie peut-être écrite par un auteur différent. <strong>N'importe qui peut écrire n'importe quelle partie de n'importe quelle histoire.</strong> Si vous vous sentez l'âme d'un créatif ou que vous recherchez des lectures originales, pourquoi ne pas commencer par visiter <a href="">la page des histoires ?</a> Vous avez des questions, <a href="">la FAQ est faite pour vous.</a></p>
                <p>Si vous voulez créer vos propres histoires ou signer automatiquement vos textes, vous pouvez vous inscrire en utilisant le formulaire ci-dessous. Moins de 20 secondes sont nécessaires pour le remplir.</p>

                <!-- Inscription form -->
                <form draggable="false" action="" method="post" id="inscription_form">
                    <table class="form_table">
                        <tbody>
                            <tr>
                                <td><label class="form_label">Pseudonyme :</label></td>
                                <td><input type="text" required="" autofocus="" name="username"></td>
                            </tr>
                            <tr>
                                <td><label class="form_label">Email :</label></td>
                                <td><input type="email" required="" name="mail"></td>
                            </tr>
                            <tr>
                                <td><label class="form_label">Mot de passe :</label></td>
                                <td><input type="password" required="" name="password"></td>
                            </tr>
                            <tr>
                                <td><label class="form_label">Mot de passe(2) :</label></td>
                                <td><input type="password" required="" name="password_verif"></td>
                            </tr>
                            <tr>
                                <td><img alt="CAPTCHA" src="/captcha/simple-php-captcha.php?_CAPTCHA&amp;t=0.13380200+1411473199"></td>
                                <td><input type="text" required="" name="captcha"></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input type="submit" value="Devenir membre" class="button"></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
                <!-- News -->
                <h2>Nouvelles du front</h2>
                <p><strong>18/09/2014 -</strong> Il est maintenant possible de partager une histoire qui vous plaît sur les réseaux sociaux, via la barre en haut de la page. D'autre part, bienvenue à Victor, notre graphiste, dans l'équipe TwentyParts.com.</p>
                <p><strong>26/08/2014 -</strong> La version française a désormais aussi sa page facebook, <a href="https://www.facebook.com/pages/TwentyPartscom-Fr/489589957852496">c'est par ici.</a></p>
                <p><strong>23/08/2014 -</strong> Je viens de mettre en ligne la version française de TwentyParts.com. La version anglaise est toujours disponible <a href="http://www.twentyparts.com/en"> sur cette page.</a> Cette mise à jour à nécessité un certain nombre de modifications internes, nous vous présentons vos excuses si vous rencontrez un quelconque bug restant.</p>
            </article>
            <?php include $this->getPath() . 'includes' . DS . 'footer.tpl.php'; ?>
        </div>
    </body>
</html>