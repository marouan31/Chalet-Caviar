<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', 'mesri1080382');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'mesri1080382');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', 'kchqq3ybxy');

/** Adresse de l’hébergement MySQL. */
define('DB_HOST', '185.98.131.91');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8mb4');

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Nk%hete%9i>;Jd9tWh;6Bn?6fHYH@{n0jI]0O LBwd^-z~+!pkhh(1nA;QQ>USa{');
define('SECURE_AUTH_KEY',  'O9Yf/[%o7:bL3[})`58?k~JPN{$.3WJz63/Z w^b5kv*v#)mv8tpq&;c&SZOuXJ/');
define('LOGGED_IN_KEY',    '(iZJAV{F B[8~A/!Tb/}NB?EY%JK~zn8?,gQ9#7SI[2WaU*bXhViUl-c#5n()c/k');
define('NONCE_KEY',        '1-/IVp,PHh<({wR]c@~:Z~Al[Tx5LeA)0&647JwMI%y1f}fe[B0|M<{~cuoR9oNa');
define('AUTH_SALT',        'jRt,Dbf&^bfY{i2VMm(KGk1SRrsJl]a![/+j<L7lC(b-W-g*7W:gja>U{%)%H!03');
define('SECURE_AUTH_SALT', 'XZEijJ6E(+^nb>RrYoFs)onTQc#6r$`ac`XxBo@j;x`Dy<M+CT::glFYz39)$OQ_');
define('LOGGED_IN_SALT',   'a#Yv~p:#JmkS1=`gD#sud)6u>c]d<+4U)B[5O:;qW}y6BE!XAzc99-X#_D~9hiDB');
define('NONCE_SALT',       'D8eB2CvcY4^Q`+3/rJ9aH;x=pJUFV(}u-Qu}#9.h7t_{AXV~0 CmrhG[dO2;z<8t');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix  = 'chalets_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');