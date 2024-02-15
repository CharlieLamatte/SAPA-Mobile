dans lib, on a :
    /Models
        => contient des modeles (notamment pour la page d'accueil et la messagerie)

    /Old
        => c'est là que vous trouverez tous vos anciens fichiers (normalement les imports sont à
           jours), je vous laisse remettre dans Screens vos fichiers à la bonne place selon la
           description ci-dessous et remettre les imports à jour avec leur nouvelle place :)

    /Screens
    /Screens/Components
        => pour les components utiles à tous les modules (appbar, navdrawer, titre, etc.)
    /Screens/Components/Formulaires
        => idem mais spécifique aux formulaires (textformfield, bouton)
    /Screens/Templates
        => pour les templates, notamment de la ScrollView et la page de formulaire

    Pour tous les modules :
    /Screens/Module_[nom_du_module]
        => toutes les pages qui concernent [nom_du_module]
           (là vous vous organisez comme vous voulez en essayant que ce soit le plus clair pour les
            prochains et en respectant la nomenclature (cf. plus bas))
    /Screens/Module_[nom_du_module]/[nom_du_module]_Components
        => tous les components spécifiques au module [nom_du_module]

    /Screens/main.dart
        => le main qu'on utilisera à la fin de l'appli
           (je vous conseille de copier/coller ce main, de le renommer en mainTODELETE.dart en le
            mettant dans votre .gitignore comme ça chacun a le sien en attendant de finir l'appli)

    /Screens/Utils
        => fichier(s) concernant la subsistance d'une personne connecté


La nomenclature :
    => Nom_DeDossier (le _ est mis si il y a besoin d'une séparation,
                      par ex : Module_MaProgression, on peut considérer que MaProgression est en "un" mot)
    => nomDeFichier.extension

