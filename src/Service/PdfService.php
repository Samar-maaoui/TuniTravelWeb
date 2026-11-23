<?php
    namespace App\Service;
    use Dompdf\Dompdf;
    use Dompdf\Options;
    class PdfService
    {
        private Dompdf $dompdf;

        public function __construct(string $binary = null) // ajouter $binary facultatif
        {
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $this->dompdf = new Dompdf($options);

            // On ignore $binary, il ne sert à rien ici
        }

        public function generatePdf(string $html): string
        {
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4', 'portrait');
            $this->dompdf->render();

            return $this->dompdf->output();
        }
    }
