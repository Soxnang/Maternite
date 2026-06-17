<?php
class StatistiqueController {
    public function dashboard(): array { return ['total_patients' => 0, 'total_dossiers' => 0]; }
    public function rapport(string $periode): array { return []; }
}
