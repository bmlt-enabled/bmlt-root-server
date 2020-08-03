<?php
/***********************************************************************/
/** This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.*/
    defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.
    
    global $comdef_install_wizard_strings;
    
    $comdef_install_wizard_strings = array (
                                            'Database_Version_Error'        =>  'FEJL: Du skal have PHP Version 5.6 eller nyere installeret på denne server!',
                                            'Database_PDO_Error'            =>  'FEJL: Du har ikke PHP PDO installeret!',
                                            'Database_Type_Error'           =>  'FEJL: Selvom du har PDO, har du ikke installeret databasedrivere!',
                                            'Database_Type_MySQL_Error'     =>  'FEJL: Selvom du har PDO, og du har installeret database drivere, er ingen af MySQL (den eneste understøttede driver)!',
                                            'Database_TestButton_Text'      =>  'TEST',
                                            'Database_TestButton_Success'   =>  'Databaseforbindelsen var vellykket.',
                                            'Database_TestButton_Fail'      =>  'Databaseforbindelsen mislykkedes: ',
                                            'Database_TestButton_Fail2'     =>  'Databaseforbindelsen mislykkedes, fordi der allerede er en initialiseret database.',
                                            'Database_Whitespace_Note'      =>  'Warning: %s has whitespace at the beginning or end.',

                                            'AJAX_Handler_DB_Connect_Error' =>  'Databaseforbindelsen mislykkedes! Sørg for, at databasen eksisterer, ER HELT TOM, brugeren er oprettet, og den bruger har fuld tilladelse på den tomme database.',
                                            'AJAX_Handler_DB_Established_Error' =>  'Databasen eksisterer allerede, og er blevet oprettet! Du kan ikke bruge denne opsætning til at overskrive en eksisterende database!',
                                            'AJAX_Handler_DB_Incomplete_Error'  =>  'Der er ikke nok information til at initialisere databasen!',
                                            
                                            'NoDatabase_Note_AlreadySet'    =>  'Der er allerede en eksisterende database, så du kan ikke initialisere en ny.',
                                            'NoDatabase_Note_PasswordIssue' =>  'Du skal oprette en serveradministratorkonto, før databasen kan initialiseres.',
                                            'NoServerAdmin_Note_AlreadySet' =>  'Der findes allerede en eksisterende database, så du kan ikke oprette en serveradministratorkonto (der eksisterer allerede en).',
                                            'NeedLongerPasswordNote'        =>  'Denne adgangskode er for kort. Det skal være mindst %d tegn langt.',
                                            
                                            'Prev_Button'                   =>  'FORRIGE',
                                            'Next_Button'                   =>  'NÆSTE',

                                            'Page_1_Tab'                    =>  'TRIN 1: Database',
                                            'Page_1_Heading'                =>  'Indstillinger Database Forbindelse',
                                            'Page_1_Text'                   =>  'Inden du kan anvende indstillingerne på denne side, skal du oprette en ny FULDSTÆNDIG TOM database og oprette en database bruger, der har fuld brugerrettigheder på den pågældende database.',
                                            
                                            'Database_Name'                 =>  'Database Navn:',
                                            'Database_Name_Default_Text'    =>  'Indsæt et Database Navn',
                                            'Database_Type'                 =>  'Database Type:',
                                            'Database_Host'                 =>  'Database Host:',
                                            'Database_Host_Default_Text'    =>  'Indsæt en Database Host',
                                            'Database_Host_Additional_Text' =>  'Dette er normalt "localhost."',
                                            'Table_Prefix'                  =>  'Table Prefix:',
                                            'Table_Prefix_Default_Text'     =>  'Indsæt et Table Prefix',
                                            'Table_Prefix_Additional_Text'  =>  'Kun for flere root-servere, der deler en database.',
                                            'Database_User'                 =>  'Database Bruger:',
                                            'Database_User_Default_Text'    =>  'Indsæt et Database Bruger Navn',
                                            'Database_PW'                   =>  'Database Password:',
                                            'Database_PW_Default_Text'      =>  'Indsæt et Database Password',
                                            'Database_PW_Additional_Text'   =>  'Opret dette som et svært, vanskelig adgangskode. Det har en masse magt, og du behøver aldrig at huske det.',

                                            'Maps_API_Key_Warning'          =>  'ADVARSEL: Der er et problem med API-nøglen til Google Maps.',
                                            'Maps_API_Key_Not_Set'          =>  'ADVARSEL: Google Maps API-nøglen er ikke angivet.',
                                            'Maps_API_Key_Valid'            =>  'Google Maps API nøgle er gyldig.',
                                            
                                            'Page_2_Tab'                    =>  'Trin 2: Indstillinger for Google Maps API',
                                            'Page_2_Heading'                =>  'Indstillinger for Google Maps API',
                                            'Page_2_API_Key_Prompt'         =>  'Enter the Google API Key for Geocoding:',
                                            'Page_2_API_Key_Set_Button'     =>  'TESTSØGLE',
                                            'Page_2_API_Key_Not_Set_Prompt' =>  'SÆT API KEY FØRST',
                                            'Page_2_Text'                   =>  'Når du gemmer et møde, bruger BMLT Root Server API en til Google Maps til at bestemme bredde og længdegrad for mødeadressen. Disse indstillinger er nødvendige for at tillade, at BMLT Root Server kommunikerer med Google Maps API.',

                                            'Page_3_Tab'                    =>  'TRIN 3: Serverindstillinger',
                                            'Page_3_Heading'                =>  'Indstil forskellige globale serverindstillinger',
                                            'Page_3_Text'                   =>  'Dette er nogle få indstillinger, der påvirker administrationen og den generelle konfiguration af denne server. De fleste serverindstillinger udføres på selve serveren.',
                                            'Admin_Login'                   =>  'Login til serveradministrator:',
                                            'Admin_Login_Default_Text'      =>  'Indsæt et serveradministrator login',
                                            'Admin_Login_Additional_Text'   =>  'Dette er login-streng for serveradministratoren.',
                                            'Admin_Password'                =>  'Server Administrator Password:',
                                            'Admin_Password_Default_Text'   =>  'Indsæt en serveradministratoradgangskode',
                                            'Admin_Password_Additional_Text'    =>  'Sørg for, at dette er et ikke-trivielt kodeord! Det har en masse betydning! (Og Glem det ikke).',
                                            'ServerAdminName'               =>  'Server Administrator',
                                            'ServerAdminDesc'               =>  'Main Server Administrator',
                                            'ServerLangLabel'               =>  'Standard server sprog:',
                                            'DistanceUnitsLabel'            =>  'Afstandsenheder:',
                                            'DistanceUnitsMiles'            =>  'Miles',
                                            'DistanceUnitsKM'               =>  'Kilometer',
                                            'SearchDepthLabel'              =>  'Tæthed af møder ved automatisk søgning:',
                                            'SearchDepthText'               =>  'Dette er en tilnærmelse af, hvor mange møder der skal findes i det automatiske radiusvalg. Flere møder betyder en større radius.',
                                            'HistoryDepthLabel'             =>  'Hvor mange møder ændringer skal gemmes:',
                                            'HistoryDepthText'              =>  ' Jo længere historie, desto større bliver databasen.',
                                            'TitleTextLabel'                =>  'Titel på administrations Knappen:',
                                            'TitleTextDefaultText'          =>  'Indsæt en kort titel for redigerings login siden',
                                            'BannerTextLabel'               =>  'Hurtig administrator login:',
                                            'BannerTextDefaultText'         =>  'Indsæt en kort forespørgsel til login side',
                                            'RegionBiasLabel'               =>  'Favorit Region:',
                                            'PasswordLengthLabel'           =>  'Minimum adgangskode længde:',
                                            'PasswordLengthExtraText'       =>  'Dette vil også påvirke Server Administrator adgangskode, ovenfor.',
                                            'DefaultClosedStatus'           =>  'Møder betragtes som "LUKKET" som Udgangspunkt:',
                                            'DefaultClosedStatusExtraText'  =>  'Dette påvirker primært eksporten til NAWS.',
                                            'DurationLabel'                 =>  'Standard Møde Varighed:',
                                            'DurationHourLabel'             =>  'Timer',
                                            'DurationMinutesLabel'          =>  'Minutter',
                                            'LanguageSelectorEnableLabel'   =>  'Vis sprogvalg ved login:',
                                            'LanguageSelectorEnableExtraText'   =>  'Hvis du vælger dette, vises en pop op-menu på login-skærmen, så administratorer kan vælge deres sprog.',
                                            'SemanticAdminLabel'            =>  'Aktivér semantisk administration:',
                                            'SemanticAdminExtraText'        =>  'Hvis ikke markeret, skal al administration ske via Root Server login (Ingen apps).',
                                            'EmailContactEnableLabel'       =>  'Tillad e-mail-kontakter fra møder:',
                                            'EmailContactEnableExtraText'   =>  'Hvis du vælger dette, vil besøgende kunne sende e-mails fra møde optegnelser.',
                                            'EmailContactAdminEnableLabel'      =>  'Inkluder Service Enhed Administrator på disse e-mails:',
                                            'EmailContactAdminEnableExtraText'  =>  'Sender kopier af disse e-mails til service enhedens administrator (hvis de ikke er den primære modtager).',
                                            'EmailContactAllAdminEnableLabel'       =>  'Medtag alle service enhedsadministratorer på disse e-mails:',
                                            'EmailContactAllAdminEnableExtraText'   =>  'Send kopier af disse e-mails til alle relevante Service Enheds Administrators.',
                                            
                                            'Page_4_Tab'                    =>  'TRIN 4: Gem indstillingerne',
                                            'Page_4_Heading'                =>  'Opret indstillingsfilen',
                                            'Page_4_Text'                   =>  'På grund af sikkerhedsproblemer (Ja, vi er ret paranoid -go figur), vil dette program ikke forsøge at oprette eller ændre indstillingsfilen. I stedet beder vi dig om at oprette det selv, via FTP eller en kontrolpanel filhåndtering, navngiv det "auto-config.inc.php" og indsæt følgende tekst i filen:',
                                            
                                            'DefaultPasswordLength'         =>  10,
                                            'DefaultMeetingCount'           =>  10,
                                            'DefaultChangeDepth'            =>  5,
                                            'DefaultDistanceUnits'          =>  'mi',
                                            'DefaultDurationTime'           =>  '01:30:00',
                                            'DurationTextInitialText'       =>  'N.A. Møder er normalt 60 minutter lange (en time), medmindre andet er angivet.',
                                            'time_format'                   =>  'g:i A',
                                            'change_date_format'            =>  'g:i A, n/j/Y',
                                            'BannerTextInitialText'         =>  'Administration Login',
                                            'TitleTextInitialText'          =>  'Basic Meeting List Toolbox Administration',
                                            'DefaultRegionBias'             =>  'us',
                                            'search_spec_map_center'        =>  array ( 'Længdegrad' => -118.563659, 'Højdegrad' => 34.235918, 'zoom' => 6 ),
                                            'DistanceChoices'               =>  array ( 2, 5, 10, 20, 50 ),
                                            'HistoryChoices'                =>  array ( 1, 2, 3, 5, 8, 10, 15 ),
                                            'PW_LengthChices'               =>  array ( 6, 8, 10, 12, 16 ),
                                            'ServerAdminDefaultLogin'       =>  'serveradmin',
                                            
                                            'Explanatory_Text_1_Initial_Intro'  =>  'Denne installationsguiden fører dig gennem processen med oprettelse af en startdatabase samt en konfigurationsfil. I det sidste trin opretter vi en indstillingsfil og initialiserer en tom database.',
                                            'Explanatory_Text_1_DB_Intro'       =>  'Den første ting, du skal gøre, er at oprette en ny, TOM database og en database bruger, der har fuld adgang til databasen. Dette gøres normalt via dit websted Kontrolpanel. Når du har oprettet databasen, skal du indtaste oplysningerne om den pågældende database i tekstelementerne på denne side.',

                                            'Explanatory_Text_2_Region_Bias_Intro'  =>  'Den "Region Bias" er en kode, der er sendt til Google, når en placering søgningen er færdig, og kan hjælpe Google til at få mening ud af tvetydige søgeforespørgsler.',
                                            'Explanatory_Text_2_API_key_Intro'      =>  '"API-nøgle" er en nøgle, som <a target = "_ blank" title = "Følg dette link for at gå til en side, der diskuterer Google API-nøglen." href="https://bmlt.app/google-maps-api-keys-and-geolocation-issues/">you need to register with Google</a> in order to be able to use their mapping service.',
                                            'Explanatory_Text_2_API_key_2_Intro'    =>  'Du skal angive en gyldig API-nøgle for at oprette nye møder i Rootserveren.',

                                            'Explanatory_Text_3_Server_Admin_Intro' =>  'Serveradministratoren er hovedbrugeren til serveren. Det er den eneste konto, der kan skabe nye brugere og serviceorganer, og er meget kraftfuld. Du skal oprette et login-id og en ikke-triviel adgangskode til denne konto. Du kan ændre de øvrige aspekter af kontoen på hovedserveren, når databasen er oprettet.',
                                            'Explanatory_Text_3_Misc_Intro'     =>  'Disse er forskellige indstillinger, der påvirker hvordan rodserveren opfører sig og vises.',
                                            
                                            'Explanatory_Text_4_Main_Intro'     =>  'Hvis du har indtastet database oplysningerne, og hvis du har angivet loginoplysningerne til serveradministratoren, kan du initialisere databasen her. Husk at databasen skal være HELT TOM for BMLT Root Server tabeller for denne server (Det kan have tabeller til andre servere eller tjenester).',
                                            'Explanatory_Text_4_File_Intro'     =>  'Teksten i boksen nedenfor er PHP kildekoden for hovedindstillingsfilen. Du skal oprette en fil på serveren med denne tekst i den. Filen er på samme niveau som hovedservermappen for rodserveren.',
                                            'Explanatory_Text_4_File_Extra'     =>  'Du skal også sikre dig, at filtilladelserne er begrænset (chmod 0644). Dette forhindrer filen i at blive skrevet, og rodserveren kører ikke, medmindre filen har de korrekte tilladelser.',
                                            'Page_4_PathInfo'                   =>  'Filen skal placeres som %s/auto-config.inc.php, hvor din% s-mappe er. Når filen er oprettet, og du har lagt ovenstående tekst i den, skal du udføre følgende kommando for at sikre, at tilladelserne er korrekte:',
                                            'Page_4_Final'                      =>  'Når alt dette er færdig, opdatere denne side, og du bør kunne se root serveren login siden.',
                                            'FormatLangNamesLabel'              =>  'Enter extra languages in format code1:name1 (example "fa:farsi ru:russian"):',
        
                                        );
