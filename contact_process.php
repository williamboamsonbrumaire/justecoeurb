<?php
// Vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Récupération et sécurisation des données
    $nom           = htmlspecialchars(trim($_POST['nom']));
    $email_expediteur = htmlspecialchars(trim($_POST['email']));
    $type_demande  = htmlspecialchars(trim($_POST['type_demande']));
    $message       = htmlspecialchars(trim($_POST['message']));

    // 2. Vérification rapide des champs obligatoires
    if (empty($nom) || empty($email_expediteur) || empty($message) || empty($type_demande)) {
        // Redirection vers la page d'accueil ou une page d'erreur si des champs sont vides
        header("Location: index.html?status=error&msg=Veuillez remplir tous les champs.");
        exit;
    }

    // 3. Configuration de l'e-mail
    $destinataire = "williamboamsonbrumaire@gmail.com"; // <-- REMPLACEZ PAR VOTRE ADRESSE
    $sujet        = "Nouvelle demande - Type: " . $type_demande;
    $headers      = "From: " . $nom . " <" . $email_expediteur . ">\r\n";
    $headers     .= "Reply-To: " . $email_expediteur . "\r\n";
    $headers     .= "MIME-Version: 1.0\r\n";
    $headers     .= "Content-type: text/html; charset=UTF-8\r\n";
    
    // Corps de l'e-mail au format HTML pour une meilleure lisibilité
    $contenu_email = "
    <html>
    <head>
      <title>Nouvelle demande de contact</title>
    </head>
    <body>
      <h2>Détails de la demande</h2>
      <p><strong>Nom & Organisation:</strong> {$nom}</p>
      <p><strong>E-mail:</strong> {$email_expediteur}</p>
      <p><strong>Type de demande:</strong> {$type_demande}</p>
      <hr>
      <h3>Message:</h3>
      <p>{$message}</p>
      <hr>
    </body>
    </html>
    ";

    // 4. Envoi de l'e-mail
    if (mail($destinataire, $sujet, $contenu_email, $headers)) {
        // Succès : Redirige l'utilisateur vers la page d'accueil avec un message de succès
        header("Location: index.html?status=success");
    } else {
        // Échec de l'envoi
        header("Location: index.html?status=error&msg=Erreur lors de l'envoi de l'email.");
    }
    
} else {
    // Si la page est accédée directement sans formulaire
    header("Location: index.html");
}

exit; // Toujours mettre exit après une redirection
?>