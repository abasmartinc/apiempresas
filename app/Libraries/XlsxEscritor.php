<?php

namespace App\Libraries;

/**
 * Escribe un archivo Excel (.xlsx) fila a fila, sin cargar el listado en memoria.
 *
 * Por qué existe (02-10-2026): los listados se entregaban solo en CSV. Según la
 * configuración regional, Excel abre un CSV con todo en una columna o con las tildes
 * rotas, y la gente pide "un Excel". Aquí todas las celdas son texto (un CIF o un
 * teléfono no se convierten en número ni pierden ceros), la primera fila va en negrita,
 * fija y con filtro.
 *
 * Sin librerías: un .xlsx es un ZIP con XML. Las filas se escriben en un fichero
 * temporal según llegan y al final se empaqueta con ZipArchive. La memoria no crece
 * con el número de filas.
 *
 *   $x = new XlsxEscritor(['Empresa', 'CIF']);
 *   $x->fila(['ACME SL', 'B12345678']);
 *   $x->guardar('/ruta/listado.xlsx');
 */
class XlsxEscritor
{
    /** Límite de filas de una hoja de Excel (incluida la cabecera) */
    public const MAX_FILAS_EXCEL = 1048576;

    /** @var resource|null */
    private $hoja;
    private string $rutaHoja;
    private int $fila = 0;
    private int $columnas;

    public static function disponible(): bool
    {
        return class_exists(\ZipArchive::class);
    }

    /**
     * @param string[] $cabeceras
     * @param int[]    $anchos    ancho de cada columna en caracteres (opcional)
     */
    public function __construct(array $cabeceras, array $anchos = [])
    {
        $this->columnas = count($cabeceras);
        $this->rutaHoja = (string) tempnam(sys_get_temp_dir(), 'xlsxhoja');
        $this->hoja     = fopen($this->rutaHoja, 'w');
        if (!$this->hoja) {
            throw new \RuntimeException('No se pudo crear el fichero temporal del Excel');
        }

        fwrite($this->hoja, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>');
        foreach ($cabeceras as $i => $c) {
            $ancho = (int) ($anchos[$i] ?? max(12, mb_strlen((string) $c, 'UTF-8') + 4));
            fwrite($this->hoja, '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $ancho . '" customWidth="1"/>');
        }
        fwrite($this->hoja, '</cols><sheetData>');

        $this->escribirFila($cabeceras, 1);
    }

    /** Filas de datos escritas (sin contar la cabecera) */
    public function filas(): int
    {
        return max(0, $this->fila - 1);
    }

    public function fila(array $valores): void
    {
        $this->escribirFila($valores, 0);
    }

    private function escribirFila(array $valores, int $estilo): void
    {
        $this->fila++;
        $xml = '<row r="' . $this->fila . '">';
        $i   = 0;
        foreach ($valores as $v) {
            $v = self::limpiar((string) $v);
            if ($v !== '') {
                $xml .= '<c r="' . self::columna($i) . $this->fila . '" t="inlineStr"' . ($estilo ? ' s="' . $estilo . '"' : '') . '>'
                      . '<is><t xml:space="preserve">' . $v . '</t></is></c>';
            }
            $i++;
        }
        fwrite($this->hoja, $xml . '</row>');
    }

    /** 0 → A, 25 → Z, 26 → AA */
    private static function columna(int $i): string
    {
        $s = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $s = chr(65 + ($i - 1) % 26) . $s;
        }

        return $s;
    }

    /** Texto válido para XML 1.0 y dentro del límite de una celda de Excel (32.767 caracteres) */
    private static function limpiar(string $s): string
    {
        if ($s === '') {
            return '';
        }
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');   // sustituye bytes inválidos
        }
        $s = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $s) ?? '';
        if (mb_strlen($s, 'UTF-8') > 32000) {
            $s = mb_substr($s, 0, 32000, 'UTF-8');
        }

        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Cierra la hoja y escribe el .xlsx en $destino.
     */
    public function guardar(string $destino, string $nombreHoja = 'Listado'): void
    {
        if (!$this->hoja) {
            throw new \RuntimeException('El Excel ya está cerrado');
        }
        $ultima = self::columna(max(0, $this->columnas - 1)) . max(1, $this->fila);
        fwrite($this->hoja, '</sheetData><autoFilter ref="A1:' . $ultima . '"/></worksheet>');
        fclose($this->hoja);
        $this->hoja = null;

        $nombreHoja = htmlspecialchars(mb_substr(preg_replace('/[\[\]\*\?\/\\\\:]/', ' ', $nombreHoja) ?? 'Listado', 0, 31, 'UTF-8'), ENT_QUOTES | ENT_XML1, 'UTF-8');

        $zip = new \ZipArchive();
        if ($zip->open($destino, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($this->rutaHoja);
            throw new \RuntimeException('No se pudo crear el archivo Excel');
        }
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $nombreHoja . '" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');
        // Estilo 0: normal. Estilo 1: cabecera (negrita, fondo gris claro).
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE2E8F0"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>');
        $zip->addFile($this->rutaHoja, 'xl/worksheets/sheet1.xml');
        $ok = $zip->close();
        @unlink($this->rutaHoja);
        if (!$ok) {
            throw new \RuntimeException('No se pudo cerrar el archivo Excel');
        }
    }

    /** Si se abandona a medias, no deja el temporal */
    public function descartar(): void
    {
        if ($this->hoja) {
            fclose($this->hoja);
            $this->hoja = null;
        }
        @unlink($this->rutaHoja);
    }

    public function __destruct()
    {
        $this->descartar();
    }
}
