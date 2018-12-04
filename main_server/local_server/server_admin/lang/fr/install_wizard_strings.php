<?php
/***********************************************************************/
/** This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: http://bmlt.magshare.org

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this code.  If not, see <http://www.gnu.org/licenses/>.*/
    defined('BMLT_EXEC') or die('Ne peut pas s\exécuter directement'); // Makes sure that this file is in the correct context.
    
    global $comdef_install_wizard_strings;
    
    $comdef_install_wizard_strings = array (
                                            'Database_Version_Error'        =>  'ERREUR: Vous devez avoir la version de PHP 5.6 ou superieur installe sur ce serveur!',
                                            'Database_PDO_Error'            =>  'ERREUR: Vous n\'avez pas installé PHP PDO!',
                                            'Database_Type_Error'           =>  'ERREUR: Vous n\'avez pas Installe PHP PDO!',
                                            'Database_TestButton_Text'      =>  'TEST',
                                            'Database_TestButton_Success'   =>  'La connexion de base de données a reussi.',
                                            'Database_TestButton_Fail'      =>  'La connexion de base de données a echoue:',
                                            'Database_TestButton_Fail2'     =>  'La connexion de base de données a echoue car il existe deja une base de donnees initialisee.',

                                            'AJAX_Handler_DB_Connect_Error' =>  'La connexion de base de données a échoué! S\'il vous plaît assurez-vous que la base de données existe, est complètement vide, l\'utilisateur est créé, et que l\'utilisateur dispose des autorisations complètes sur la base de données vide.',
                                            'AJAX_Handler_DB_Established_Error' =>  'La base de données esists déjà, et a été mis en place! Vous ne pouvez pas utiliser cette configuration pour écraser une base existante!',
                                            'AJAX_Handler_DB_Incomplete_Error'  =>  'Il n\'y a pas assez d\'informations pour initialiser la base de données!',
                                            
                                            'NoDatabase_Note_AlreadySet'    =>  'Il existe déjà une base de données existante, de sorte que vous ne peut pas initialiser de nouveau.',
                                            'NoDatabase_Note_PasswordIssue' =>  'Vous devez créer un compte d\'administrateur du serveur avant que la base de données peut être initialisé.',
                                            'NoServerAdmin_Note_AlreadySet' =>  'Il existe déjà une base de données existante, donc vous ne pouvez pas configurer un compte d\'administrateur de serveur (Il en existe un déjà).',
                                            'NeedLongerPasswordNote'        =>  'Ce mot de passe est trop court. Il doit être au moins de %d caractères.',
                                            
                                            'Prev_Button'                   =>  'PRÉCÉDENT',
                                            'Next_Button'                   =>  'SUIVANT',

                                            'Page_1_Tab'                    =>  'ÉTAPE 1: Basse de donnée',
                                            'Page_1_Heading'                =>  'Réglages de base de données de connexion',
                                            'Page_1_Text'                   =>  'Avant de pouvoir appliquer les paramètres de cette page, vous devez mettre en place une nouvelle base de données complètement vide, et d\'établir une base de données utilisateur disposant de droits d\'accès complets sur cette base de données.',
                                            
                                            'Database_Name'                 =>  'Nom de la base de donnée:',
                                            'Database_Name_Default_Text'    =>  'Entrer le Nom de la base de donnée',
                                            'Database_Type'                 =>  'Type de la base de données:',
                                            'Database_Host'                 =>  'Hôte de la base de données:',
                                            'Database_Host_Default_Text'    =>  'Entrer Hôte de la base de données:',
                                            'Database_Host_Additional_Text' =>  'Normalement c\'est "localhost."',
                                            'Table_Prefix'                  =>  'Préfixe de la Table:',
                                            'Table_Prefix_Default_Text'     =>  'Entrer un Préfixe de la Table:',
                                            'Table_Prefix_Additional_Text'  =>  'Seulement pour les serveurs racines multiples partageant une base de données.',
                                            'Database_User'                 =>  'nom d\'utilisateur la base de données:',
                                            'Database_User_Default_Text'    =>  'Entrer le nom d\'utilisateur la base de données',
                                            'Database_PW'                   =>  'Mot de Passe de la Base de Données:',
                                            'Database_PW_Default_Text'      =>  'Entrer le Mot de Passe de la Base de Données',
                                            'Database_PW_Additional_Text'   =>  'Faite à ce qu\'il en soit vraiment sécuritaire.',

                                            'Maps_API_Key_Warning'          =>  'WARNING: There is a problem with the Google Maps API Key.',
                                            
                                            'Page_2_Tab'                    =>  'ÉTAPE 2: Localisation par défaut',
                                            'Page_2_Heading'                =>  'Regler la localisation innitiale pour les Réunions',
                                            'Page_2_Text'                   =>  'When saving a meeting, the BMLT Root Server uses the Google Maps API to determine the latitude and longitude for the meeting address. These settings are required to allow the BMLT Root Server to communicate with the Google Maps API.',

                                            'Page_3_Tab'                    =>  'ÉTAPE 3: Paramètres du serveur',
                                            'Page_3_Heading'                =>  'Définissez les paramètres généraux du serveur',
                                            'Page_3_Text'                   =>  'Ce sont quelques paramètres qui affectent l\'administration et la configuration générale de ce serveur. La plupart des paramètres du serveur sont effectuées sur le serveur lui-même.',
                                            'Admin_Login'                   =>  'Compte Administraterur du Serveur:',
                                            'Admin_Login_Default_Text'      =>  'Entrer le Compte Administraterur du Serveur',
                                            'Admin_Login_Additional_Text'   =>  'Il s\'agit de la chaîne de connexion de l\'administrateur du serveur.',
                                            'Admin_Password'                =>  'Mot de passe administrateur du serveur:',
                                            'Admin_Password_Default_Text'   =>  'Entrer le Mot de passe administrateur du serveur',
                                            'Admin_Password_Additional_Text'    =>  'Assurez-vous qu\'il s\'agit d\'un mot de passe non négligeable! Il a beaucoup de puissance! (Aussi, ne l\'oubliez pas).',
                                            'ServerAdminName'               =>  'SAdministrateur du serveur',
                                            'ServerAdminDesc'               =>  'Principale Administrateur du serveur',
                                            'ServerLangLabel'               =>  'Langue du Serveur par défaut:',
                                            'DistanceUnitsLabel'            =>  'Unités de distance:',
                                            'DistanceUnitsMiles'            =>  'Miles',
                                            'DistanceUnitsKM'               =>  'Kilomètres',
                                            'SearchDepthLabel'              =>  'Densité des réunions pour la recherche automatique:',
                                            'SearchDepthText'               =>  'Il s\'agit d\'une approximation du nombre de rencontres qui devront être trouvée dans la sélection automatique du rayon. Plus de réunions est signifie un plus grand rayon.',
                                            'HistoryDepthLabel'             =>  'Nombre de sauvegardes de changements pour une réunion:',
                                            'HistoryDepthText'              =>  'Le plus l\'historique est, plus la base de données en deviendra.',
                                            'TitleTextLabel'                =>  'Le titre de l\'écran d\'administration:',
                                            'TitleTextDefaultText'          =>  'Saisissez un titre court de l\'édition Page de Connexion',
                                            'BannerTextLabel'               =>  'Invite de la Connexion pour l\'administration:',
                                            'BannerTextDefaultText'         =>  'Entrez une invite courte pour la page de connexion',
                                            'RegionBiasLabel'               =>  'Biais de région:',
                                            'PasswordLengthLabel'           =>  'Longueur Minimale de Mot de passe:',
                                            'PasswordLengthExtraText'       =>  'Cela affectera également le mot de passe de l\'administrateur du serveur, ci-dessus.',
                                            'DurationLabel'                 =>  'Durée par défaut de la réunion:',
                                            'DurationHourLabel'             =>  'Heures',
                                            'DurationMinutesLabel'          =>  'Minutes',
                                            'LanguageSelectorEnableLabel'   =>  'Display Language Selector On Login:',
                                            'LanguageSelectorEnableExtraText'   =>  'If you select this, a popup menu will appear in the login screen, so administrators can select their language.',
                                            'EmailContactEnableLabel'       =>  'Allow Email Contacts From Meetings:',
                                            'EmailContactEnableExtraText'   =>  'If you select this, site visitors will be able to send emails from meeting records.',
                                            
                                            'Page_4_Tab'                    =>  'Étape 4: Enregistrer les paramètres',
                                            'Page_4_DB_Setup_Heading'       =>  'Initialiser une nouvelle base de données',
                                            'Page_4_DB_Setup_Text'          =>  'Le bouton ci-dessous va créer une nouvelle base de données initialisée avec les formats par défaut, pas de corps de service et un utilisateur d\'administrateur de serveur.',
                                            'Set_Up_Database'               =>  'initialiser la base de données',
                                            'Page_4_Heading'                =>  'Créer le fichier de paramètres',
                                            'Page_4_Text'                   =>  'Pour des raisons de sécurité (Oui, nous sommes assez paranoïaque - allez figure donc..), ce programme ne tentera pas de créer ou modifier le fichier de paramètres. Au lieu de cela, nous vous demandons de créer vous-même, via FTP ou un gestionnaire de fichiers panneau de contrôle, le nommer "auto-config.inc.php", et collez le texte suivant dans le fichier:',
                                            
                                            'DefaultPasswordLength'         =>  10,
                                            'DefaultMeetingCount'           =>  10,
                                            'DefaultChangeDepth'            =>  5,
                                            'DefaultDistanceUnits'          =>  'mi',
                                            'DefaultDurationTime'           =>  '01:30:00',
                                            'DurationTextInitialText'       =>  'Les Réunions de NA durent généralement de 90 minutes (une heure et demie), sauf indication contraire.',
                                            'time_format'                   =>  'g:i A',
                                            'change_date_format'            =>  'g:i A, n/j/Y',
                                            'BannerTextInitialText'         =>  'Connexion administration',
                                            'TitleTextInitialText'          =>  'Basic Meeting List Toolbox Administration',
                                            'DefaultRegionBias'             =>  'us',
                                            'search_spec_map_center'        =>  array ( 'longitude' => -118.563659, 'latitude' => 34.235918, 'zoom' => 6 ),
                                            'DistanceChoices'               =>  array ( 2, 5, 10, 20, 50 ),
                                            'HistoryChoices'                =>  array ( 1, 2, 3, 5, 8, 10, 15 ),
                                            'PW_LengthChices'               =>  array ( 6, 8, 10, 12, 16 ),
                                            'ServerAdminDefaultLogin'       =>  'serveradmin',
                                            
                                            'Explanatory_Text_1_Initial_Intro'  =>  'Cet assistant d\'installation vous guidera à travers le processus de création d\'une base de données initiale, ainsi que d\'un fichier de configuration. Dans la dernière étape, nous allons créer un fichier de paramètres, et initialiser une base de données vide.',
                                            'Explanatory_Text_1_DB_Intro'  =>  'La première chose que vous devez faire, c\'est de créer une nouvelle base de données vide, et un utilisateur de base de données qui a accès à cette base de données. Cela se fait habituellement par l\'intermédiaire de votre site Web Panneau de configuration. Une fois que vous avez créé la base de données, vous devez saisir les informations relatives à cette base de données dans les éléments de texte sur cette page.',

                                            'Explanatory_Text_2_Region_Bias_Intro'  =>  'The "Region Bias" is a code that is sent to Google when a location search is done, and can help Google to make sense of ambiguous search queries.',

                                            'Explanatory_Text_3_Server_Admin_Intro'  =>  'L\'administrateur du serveur est le principal utilisateur pour le serveur. Il est le seul compte qui peut créer de nouveaux utilisateurs et les organismes de service, et est très puissant. Vous devez créer un identifiant et un mot de passe non négligeable pour ce compte. Vous serez en mesure de modifier les autres aspects du compte sur le serveur principal, une fois la base de données a été mis en place.',
                                            'Explanatory_Text_3_Misc_Intro'  =>  'Ce sont les différents paramètres qui influent sur la façon dont le serveur racine se comporte et apparaît.',
                                            
                                            'Explanatory_Text_4_Main_Intro'  =>  'Si vous avez entré les informations de base de données, et si vous avez spécifié les informations de connexion de l\'administrateur du serveur, vous pouvez initialiser la base de données ici. Rappelez-vous que la base de données doit être complètement vide de tables pour le Serveur BMLT racine. (Il peut avoir des tables pour d\'autres serveurs ou services).',
                                            'Explanatory_Text_4_File_Intro'  =>  'Le texte ci-dessous est le code source de PHP pour le fichier de paramètres principaux. Vous aurez besoin de créer un fichier sur le serveur avec ce texte dedans. Le fichier est au même niveau que le répertoire du serveur principal pour le serveur racine.',
                                            'Explanatory_Text_4_File_Extra'  =>  'Vous devez également vous assurer que les autorisations de fichiers sont limités (chmod 0644). Cela empêche le fichier d\'être écrite, et le serveur racine ne fonctionnera que si le fichier dispose des autorisations nécessaires.',
                                            'Page_4_PathInfo'               =>  'Le fichier doit être placé comme %s/auto-config.inc.php, ce qui est l\'endroit où le répertoire de votre %s est. Une fois le fichier a été créé et vous avez mis le texte ci-dessus dedans, vous devez exécuter la commande suivante pour vous assurer que les autorisations sont correctes:',
                                            'Page_4_Final'                  =>  'Une fois tout cela terminé, actualisez cette page, et vous devriez voir la page de connexion du serveur racine.',
                                            );
