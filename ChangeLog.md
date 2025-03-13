# Change Log
All notable changes to this project will be documented in this file.

## Unreleased

## Release 2.3
- FIX : Conversion des tms en date pour les extrafields - *06/03/2025* - 2.3.7
    + Remplacement de la mention cf.action par "Aucune action précédente trouvée", pour plus de compréhension.
    + suppression de la date 01/01/1970 si aucune date renseigné
- FIX : Cast en int des dates jamais définis par défaut en string  - *25/02/2025* - 2.3.6
- FIX : Test des clés des fields  - *21/02/2025* - 2.3.5
        Suppression de $checkArrayOptions, plus nécéssaire dans la nouvelle gestion => provoquer des doublons dans l'onglet 
- FIX : Conversion des tms en date - *20/02/2025* - 2.3.4
        Ajout des trads sur les champs standard et extrafields
- FIX : missed contact class in file history.php - *17/02/2025* - 2.3.3  
- FIX : Le test sur le type des anciennes valeurs vs les nouvelles était mauvais ce qui empêchait un bon enregistrement des données.- **31/01/2025** - 2.3.2
- FIX : COMPAT V21 - *18/12/2024* - 2.3.1
- NEW : COMPAT V20 - *24/07/2024* - 2.3.0

## Release 2.2
- FIX : Warning on change user right  - *10/01/2024* - 2.2.1
- NEW TechATM + Page About - *10/01/2024* - 2.2.0
- NEW : COMPATV19 - *04/12/2023* - 2.1.0  
    Changed Dolibarr compatibility range to 15 min - 19 max  
    Changed PHP compatibility range to 7.0 min - 8.2 max

## Release 2.0
FIX : Fatal missing inclusion - *06/11/2023* - 2.0.7  
FIX : Remove htmlEntities - *08/03/2023* - 2.0.6  
FIX : Missing icon *13/07/2022* - 2.0.5
Fix : change object field type to string  " *27/05/2022* - 2.0.4
Fix : default value for null field "fk_object_deleted" *23/02/2022* - 2.0.3
Fix : compatibilité v13.0  getpost type    *22/12/2021* - 2.0.2
Fix : compatibilité v13.0  new token()    *22/12/2021* - 2.0.1
