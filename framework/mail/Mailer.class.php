<?php

namespace framework\mail;

class Mailer {

    /**
     * L'attribut qui permet de stocker le sujet du mail
     *
     * @access private
     * @var <string>
     */
    private $sujet;

    /**
     * L'attribut qui permet de stocker le message du mail
     *
     * @access private
     * @var <string>
     */
    private $message;

    /**
     * L'attribut qui permet de stocker le nom de l'expediteur du mail
     *
     * @access private
     * @var <string>
     */
    private $expediteur_nom;

    /**
     * L'attribut qui permet de stocker l'adresse email de l'expediteur du mail
     *
     * @access private
     * @var <string>
     */
    private $expediteur_email;

    /**
     * L'attribut qui permet de stocker l'adresse email du destinataire du mail
     *
     * @access private
     * @var <string>
     */
    private $destinataire_email;

    /**
     * L'attribut qui permet de stocker le nom du destinataire du mail
     *
     * @access private
     * @var <string>
     */
    private $destinataire_nom;

    /**
     * L'attribut qui permet de stocker l'entete du mail
     *
     * @access private
     * @var <string>
     */
    private $entete;

    /**
     * Permet de définir l'encodage de l'email (utf8) et son type (html)
     *
     * @access public
     * @param <void>
     * @return <void>
     */
    public function __construct($type = 'text/html', $encodage = 'UTF-8') {
        $this->type = $type;
        $this->encodage = $encodage;
    }

    /**
     * Permet de définir l'entete du mail: MIME version, les parametres(Content-type, Charset)...
     *
     * @access public
     * @param <void>
     * @return <void>
     */
    private function setEntete() {
        $params = "MIME-Version: 1.0\r\n";
        $params .= "Content-type: $this->type; charset=$this->encodage\r\n";
        $from = "From: $this->expediteur_nom <$this->expediteur_email>\r\n";
        $to = "To: $this->destinataire_nom <$this->destinataire_email>\r\n";
        $replay = "Reply-To: $this->expediteur_email\r\n";
        $this->entete = $from . $to . $replay . $params;
    }

    /**
     * Permet d'assigner une valeur à l'attribut "destinataireNom" de l'objet
     *
     * @access public
     * @param <string> $value
     * @return <void>
     */
    public function setDestinataireNom($value) {
        $this->destinataire_nom = $value;
        return $this;
    }

    /**
     * Permet d'assigner une valeur à l'attribut "destinataireEmail" de l'objet
     *
     * @access public
     * @param <string> $value
     * @return <void>
     */
    public function setDestinataireEmail($value) {
        $this->destinataire_email = $value;
        return $this;
    }

    /**
     * Permet d'assigner une valeur à l'attribut "sujet" de l'objet
     *
     * @access public
     * @param <string> $value
     * @return <void>
     */
    public function setSujet($value) {
        $this->sujet = $value;
        return $this;
    }

    /**
     * Permet d'assigner une valeur à l'attribut "expediteurNom" de l'objet
     *
     * @access public
     * @param <string> $value
     * @return <void>
     */
    public function setExpediteurNom($value) {
        $this->expediteur_nom = $value;
        return $this;
    }

    /**
     * Permet d'assigner une valeur à l'attribut "expediteurEmail" de l'objet
     *
     * @access public
     * @param <string> $value
     * @return <void>
     */
    public function setExpediteurEmail($value) {
        $this->expediteur_email = $value;
        return $this;
    }

    /**
     * Permet d'assigner une valeur à l'attribut "message" de l'objet
     *
     * @access public
     * @param <string> $value
     * @return <void>
     */
    public function setMessage($value) {
        $this->message = $value;
        return $this;
    }

    /**
     * Permet d'envoyer un mail via la fonction mail()
     *
     * @access public
     * @param <void>
     * @return <bool> retour de mail()
     */
    public function envoyer() {
        $this->setEntete();
        return mail($this->destinataire_email, $this->sujet, $this->message, $this->entete);
    }

}

?>