<?php

require('mc_indent.php');

$InterLigne = 7;

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetMargins(30, 10, 30);
$pdf->SetFont('Arial', '', 12);

$txt    = "Cher Pierre";
$txtLen = $pdf->GetStringWidth($txt);
$milieu = (210 - $txtLen) / 2;
$pdf->SetX($milieu);
$pdf->Write(5, $txt);

$pdf->ln(30);
$txt = "Voici venu le temps pour toi de renouveler ta licence-assurance, en effet celle-ci expire le 28/9 prochain. Tu trouveras joint � ce document le certificat d'aptitude � faire remplir par le m�decin.";
$pdf->MultiCell(0, $InterLigne, $txt, 0, 'J', 0, 15);

$pdf->ln(10);
$txt = "Je me permets de te rappeler que cette licence est obligatoire et n�cessaire � la pratique de notre sport favori, tant � l'occasion de nos entra�nements qu'� toutes autres manifestations auxquelles tu peux participer telles que comp�titions, cours f�d�raux ou visites amicales dans un autre club.";
$pdf->MultiCell(0, $InterLigne, $txt, 0, 'J', 0, 15);

$pdf->ln(10);
$txt = "D�s lors, je te saurais gr� de bien vouloir me retourner le certificat d'aptitude d�ment compl�t� par le m�decin accompagn� de ton paiement de 31 � ou de la preuve de celui-ci par virement bancaire. Le tout dans les plus brefs d�lais afin de ne pas interrompre la couverture de ladite assurance et par la m�me occasion de t'emp�cher de participer � nos cours le temps de la r�gularisation. Il y va de ta s�curit�.";
$pdf->MultiCell(0, $InterLigne, $txt, 0, 'J', 0, 15);

$pdf->ln(10);
$txt = "Merci de la confiance que tu mets en notre club pour ton �panouissement sportif.";
$pdf->MultiCell(0, $InterLigne, $txt, 0, 'J', 0, 15);

$pdf->ln(10);
$txt = "Le comit�";
$pdf->MultiCell(0, $InterLigne, $txt, 0, 'R', 0);

$pdf->Output();
?>
