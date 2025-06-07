<?php

namespace App\Entities;

use App\Adapters\ProcessDataFile;
use Generator;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\CSV\Options;
use OpenSpout\Reader\CSV\Reader;

class CsvFile implements ProcessDataFile
{
    /**
     * Processa o arquivo CSV com OpenSpout, extraindo colunas específicas e retornando via yield em blocos.
     *
     * @param string $localFile
     * @return Generator<array[]>
     */
    public function processFile(string $localFile, mixed $readerOptions, mixed $reader): Generator
    {
        $path = Storage::path($localFile);
        $readerOptions->FIELD_DELIMITER = ";"; // this is to be able to copy dates

        $reader->open($path);

        $colunasDesejadas = ['RptDt', 'TckrSymb', 'MktNm', 'SctyCtgyNm', 'ISIN', 'CrpnNm'];
        $indicesColunas = [];
        $headerFound = false;
        $linhaIndex = 0;
        $chunk = [];
        $chunkSize = 1000;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $linhaIndex++;
                $valores = array_map(fn($cell) => 
                    $cell->getValue(), $row->getCells());

                // pula primeira linha
                if ($linhaIndex === 1) {
                    continue;
                }

                // define cabeçalho na segunda linha
                if ($linhaIndex === 2) {
                    $headers = explode(',', $valores[0]);

                    foreach ($colunasDesejadas as $coluna) {
                        $indice = array_search($coluna, $headers);
                        if ($indice !== false) {
                            $indicesColunas[$coluna] = $indice;
                        }
                    }

                    if (count($indicesColunas) !== count($colunasDesejadas)) {
                        throw new \RuntimeException('Uma ou mais colunas desejadas não foram encontradas no CSV.');
                    }

                    $headerFound = true;
                    continue;
                }

                if (!$headerFound) {
                    throw new \RuntimeException('Cabeçalho ausente!!!');
                }

                // processa os dados
                $dados = explode(',', $valores[0]);
                $linhaProcessada = [];

                foreach ($indicesColunas as $coluna => $indice) {
                    $linhaProcessada[$coluna] = $dados[$indice] ?? null;
                }

                $chunk[] = $linhaProcessada;

                if (count($chunk) >= $chunkSize) {
                    yield $chunk;
                    $chunk = [];
                }
            }
        }

        if (!empty($chunk)) {
            yield $chunk;
        }

        $reader->close();
    }
}
