<?php

echo " Mise à jour du logo Quizzeo\n";
echo "================================\n\n";

$files = [
    'admin/dashboard.php',
    'admin/manage.php',
    'school/dashboard.php',
    'school/create_quiz.php',
    'school/edit_quiz.php',
    'school/mes_quiz.php',
    'school/view_results.php',
    'company/dashboard.php',
    'company/create_quiz.php',
    'company/edit_quiz.php',
    'company/mes_quiz.php',
    'company/view_results.php',
    'user/dashboard.php',
    'user/profile.php',
    'user/join.php',
    'user/take_quiz.php',
    'auth/login.php',
    'auth/register.php'
];

$patterns_and_replacements = [
    [
        'old' => '<a href="dashboard.php" class="navbar-brand">QUIZZEO</a>',
        'new' => '<a href="dashboard.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>'
    ],
    [
        'old' => '<a href="../index.php" class="navbar-brand">QUIZZEO</a>',
        'new' => '<a href="../index.php" class="navbar-brand"><img src="../assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>'
    ],
    [
        'old' => '<a href="index.php" class="navbar-brand">QUIZZEO</a>',
        'new' => '<a href="index.php" class="navbar-brand"><img src="assets/images/logo.webp" alt="Quizzeo" class="navbar-logo"></a>'
    ]
];

$updated_count = 0;
$not_found_count = 0;

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $original_content = $content;
        
        foreach ($patterns_and_replacements as $pattern) {
            $content = str_replace($pattern['old'], $pattern['new'], $content);
        }
        
        if ($content !== $original_content) {
            file_put_contents($file, $content);
            echo " Mis à jour : $file\n";
            $updated_count++;
        } else {
            echo " Aucun changement : $file\n";
        }
    } else {
        echo " Fichier introuvable : $file\n";
        $not_found_count++;
    }
}

echo "\n================================\n";
echo "Résumé :\n";
echo "  Fichiers mis à jour : $updated_count\n";
echo "  Fichiers introuvables : $not_found_count\n";
echo "\n";

echo " N'oublie pas d'ajouter le CSS pour le logo !\n";
echo "   Ajoute ce code dans assets/css/style.css :\n\n";

echo ".navbar-logo {\n";
echo "    height: 50px;\n";
echo "    width: auto;\n";
echo "    transition: transform 0.3s ease;\n";
echo "}\n\n";
echo ".navbar-logo:hover {\n";
echo "    transform: scale(1.1) rotate(-2deg);\n";
echo "}\n\n";

echo "Terminé ! Recharge ton site pour voir le nouveau logo.\n";
?>