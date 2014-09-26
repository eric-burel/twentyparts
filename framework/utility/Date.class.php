<?php

namespace framework\utility;

class Date extends \DateTime {
    
    const SECOND = 1;
    const MINUTE = 60;
    const HOUR = 3600;
    const DAY = 86400;
    const WEEK = 604800;
    const MONTH = 2629800;
    const YEAR = 31557600;

    /**
     * Constructeur: permet d'instancier la class DateTime avec la date actuelle
     *
     * @access public
     * @param string $date la date actuelle
     * @return void
     *
     * @see DateTime
     */
    public function __construct($date = 'now') {
        if (!extension_loaded('date'))
            throw new \Exception('date extension not loaded try change your PHP configuration');
        // Initialisation de l'objet DateTime
        try {
            parent::__construct($date);
        } catch (\Exception $e) {
            throw new \Exception('Failed to parse time string on DateTime');
        }
    }

    /**
     * Affiche la date au format 00/00/0000 à 00hr00
     *
     * @access public
     * @static
     * @param string $value la date à afficher au bon format
     * @return string la value au format: 00/00/0000 à 00hr00
     */
    public static function dateFromUsFormat($value) {
        $temp = explode(' ', $value);
        $temp2 = array_reverse(explode('-', $temp[0]));
        return $temp2[0] . '/' . $temp2[1] . '/' . $temp2[2] . ' à ' . $temp[1];
    }

    /**
     * Génération de la liste des années
     *
     * @access public
     * @param int $limit la limite du nombre d'année
     * @return array $years liste des années à partir de l'année courante
     */
    public function getYears($limit = 90) {
        $years = array();
        $now_year = date('Y');
        for ($i = 0; $i < $limit; $i++) {
            $years[] = $now_year - $i;
        }
        return $years;
    }

    /**
     * Génération de la liste des jours
     *
     * @access public
     * @static
     * @param void
     * @return array $days liste des jours au format numérique
     */
    public static function getDays() {
        $days = array();
        for ($i = 1; $i <= 31; $i++) {
            if ($i < 10)
                $days[$i] = '0' . $i;
            else
                $days[$i] = $i;
        }
        return $days;
    }

    /**
     * Génération de la liste des mois au format numeric: 01, 02 , 03... 12
     *
     * @access public
     * @static
     * @param void
     * @return array $months liste des mois au format numérique
     */
    public function getMonths() {
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            if ($i < 10)
                $months[$i] = '0' . $i;
            else
                $months[$i] = $i;
        }
        return $months;
    }

    /**
     * Génération de la liste des mois en français
     *
     * @access public
     * @static
     * @param void
     * @return array $months liste des jours en français
     */
    public function getFrenchMonths() {
        $months = array(
            1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        );
        return $months;
    }

    /**
     * Vérifie qu'une date de naissance est valide
     *
     * @access public
     * @static
     * @param string $day le jour de naissance
     * @param string $month le mois de naissance
     * @param string $year l'année de naissance
     * @return bool retour de checkdate()
     */
    public static function checkBirthDate($day, $month, $year) {
        if (empty($day) || empty($month) || empty($year))
            return false;

        return checkdate($month, $day, $year);
    }

    /**
     * Permet de calculer un age depuis une date de naissance
     *
     * @access public
     * @param string $day le jour de naissance
     * @param string $month le mois de naissance
     * @param string $year l'année de naissance
     * @return string $years l'age
     */
    public function getAge($day, $month, $year) {
        $years = date('Y') - $year;
        if (date('m') < $month)
            $years--;
        elseif (date('d') < $day && date('m') == $month)
            $years--;

        return $years;
    }

    /**
     * Permet de definir le fuseau horaire d'une date
     *
     * @access public
     * @static
     * @param string $timezone fuseau horaire
     * @return void
     */
    public static function setDateDefaultTimezone($timezone) {
        // TODO check timezone ???
        date_default_timezone_set($timezone);
    }

    /**
     * Permet d'obtenir le fuseau horaire sur lequel php est configuré
     *
     * @access public
     * @static
     * @param void
     * @return void
     */
    public static function getDateDefaultTimezone() {
        return date_default_timezone_get();
    }

}

?>