<?php
namespace app\controllers;

use app\core\Controller;
use app\repositories\MarcaInpiRepository;

class BuscaColisaoController extends Controller
{
    public function buscar()
    {
        $this->autenticacaoRequired();
        $termo = trim($_GET['termo'] ?? '');
        $classeNice = (int)($_GET['classe_nice'] ?? 0);
        $resultados = [];
        $erro = null;
        $repository = new MarcaInpiRepository();

        $tabelaExiste = $repository->tabelaExiste();

        if (!$tabelaExiste) {
            $erro = 'A base de marcas do INPI ainda não foi instalada neste banco. Execute a migração do banco antes de importar a RPI.';
        } elseif ($termo !== '') {
            if ($classeNice < 1 || $classeNice > 45) {
                $erro = 'Selecione uma classe Nice para realizar a busca.';
            } else {
                $resultados = $repository->buscarColisoes($termo, $classeNice, 70);
            }
        }

        $this->view('busca/busca_colisao', [
            'termo' => $termo,
            'classe_nice' => $classeNice,
            'resultados' => $resultados,
            'erro' => $erro,
            'resumoBase' => $repository->resumoBase()
        ]);
    }
}
