<?php
// Définit l'adresse e-mail qui apparaîtra comme l'expéditeur.
// C'est CRUCIAL : Utilisez TOUJOURS une adresse e-mail qui existe
// et qui appartient au domaine d'où part le script (votre site web).
$email_domaine_serveur = "contact@justecoeurb.ht"; // <-- REMPLACEZ CECI
$nom_site = "Juste-Cœur Beaubrun - Site Officiel"; // <-- REMPLACEZ CECI

// Adresse e-mail de destination (où vous recevrez le formulaire)
$destinataire = "justecoeurb@gmail.com"; 


// Vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Récupération et sécurisation des données
    $nom              = htmlspecialchars(trim($_POST['nom']));
    $email_expediteur = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); // Nettoyage de l'e-mail
    $type_demande     = htmlspecialchars(trim($_POST['type_demande']));
    $message          = htmlspecialchars(trim($_POST['message']));

    // 2. Vérification rapide des champs obligatoires
    if (empty($nom) || empty($email_expediteur) || empty($message) || empty($type_demande) || !filter_var($email_expediteur, FILTER_VALIDATE_EMAIL)) {
        // Redirection vers la page d'accueil ou une page d'erreur si des champs sont vides ou l'e-mail invalide
        header("Location: index.html?status=error&msg=Veuillez remplir tous les champs correctement.");
        exit;
    }

    // 3. Configuration de l'e-mail
    $sujet        = "Nouvelle demande - Type: " . $type_demande;
    
    // Construction des En-têtes (Headers)
    // From: Doit utiliser l'e-mail du domaine pour la délivrabilité
    $headers  = "From: " . $nom_site . " <" . $email_domaine_serveur . ">\r\n";
    // Reply-To: Contient l'e-mail du visiteur pour vous permettre de répondre
    $headers .= "Reply-To: " . $nom . " <" . $email_expediteur . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    
    // Corps de l'e-mail au format HTML pour une meilleure lisibilité
    $contenu_email = "
    <html>
    <head>
      <title>Nouvelle demande de contact</title>
      <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        h2, h3 { color: #0056b3; border-bottom: 2px solid #eee; padding-bottom: 5px; }
        strong { font-weight: bold; }
        .message-content { background: #f9f9f9; padding: 15px; border-radius: 3px; border-left: 5px solid #0056b3; white-space: pre-wrap; }
      </style>
    </head>
    <body>
      <div class='container'>
        <h2>Détails de la demande</h2>
        <p><strong>Nom & Organisation:</strong> {$nom}</p>
        <p><strong>E-mail:</strong> {$email_expediteur}</p>
        <p><strong>Type de demande:</strong> {$type_demande}</p>
        <hr>
        <h3>Message:</h3>
        <div class='message-content'>{$message}</div>
      </div>
    </body>
    </html>
    ";

    // 4. Envoi de l'e-mail
    // Utilisation de @mail pour supprimer les avertissements qui pourraient apparaître lors de l'envoi
    if (@mail($destinataire, $sujet, $contenu_email, $headers)) {
        // Succès : Redirige l'utilisateur vers la page d'accueil avec un message de succès
        header("Location: index.html?status=success");
    } else {
        // Échec de l'envoi
        header("Location: index.html?status=error&msg=Erreur lors de l'envoi de l'email par le serveur.");
    }
    
} else {
    // Si la page est accédée directement sans formulaire
    header("Location: index.html");
}

exit; // Toujours mettre exit après une redirection
?>