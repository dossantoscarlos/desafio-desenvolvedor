<?php

namespace App\Entities;

use App\Adapters\ProcessDataFile;
use Generator;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

class XlsxFile implements ProcessDataFile
{
    /**
     * Processa o arquivo XLSX, extraindo colunas específicas e salvando os dados.
     *
     * @param string $localFile
     * @return void
     */
    public function processFile(string $localFile, mixed $readerOptions, mixed $reader): Generator
    {

        $path = Storage::path($localFile);
        
        $readerOptions->SHOULD_FORMAT_DATES = true; // this is to be able to copy dates
        
        $reader->open($path);

        $colunasDesejadas = ['RptDt', 'TckrSymb', 'MktNm', 'SctyCtgyNm', 'ISIN', 'CrpnNm'];
        $indicesColunas = [];
        $headerFound = false;
        $bloco = [];
        $chunkSize = 10000;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells =[];
                
                foreach ($row->getCells() as $cell) {
                    $cells[] = $cell->getValue();
                }

                if ($rowIndex === 1) {
                    continue;
                }

                if ($rowIndex === 2) {
                    foreach ($colunasDesejadas as $coluna) {
                        $indice = array_search($coluna, $cells);
                        if ($indice !== false) {
                            $indicesColunas[$coluna] = $indice;
                        }
                    }

                    if (count($indicesColunas) !== count($colunasDesejadas)) {
                        throw new \RuntimeException('Colunas desejadas não encontradas.');
                    }

                    $headerFound = true;
                    continue;
                }

                if (!$headerFound) {
                    throw new \RuntimeException('Cabeçalho ausente!!!');
                }

                $linhaProcessada = [];
                foreach ($indicesColunas as $coluna => $indice) {
                    $linhaProcessada[$coluna] = $cells[$indice] ?? null;
                }

                $bloco[] = $linhaProcessada;

                if (count($bloco) >= $chunkSize) {
                    yield $bloco;
                    $bloco = [];
                }
            }
        }

        if (!empty($bloco)) {
            yield $bloco;
        }

        $reader->close();
    }
}
