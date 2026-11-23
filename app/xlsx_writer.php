<?php
// app/xlsx_writer.php - Generador nativo de libros Excel (.xlsx / Office Open XML) con diseño corporativo DENTIX (RF-043)
// 100 % PHP puro: no requiere Composer, PhpSpreadsheet ni la extensión ZipArchive.

final class DentixXlsxWriter
{
    /** Índices de estilos definidos en styles.xml (cellXfs). */
    public const S_DEFAULT      = 0;
    public const S_TITLE        = 1;
    public const S_SUBTITLE     = 2;
    public const S_HEADER       = 3;
    public const S_TEXT         = 4;
    public const S_INT          = 5;
    public const S_MONEY        = 6;
    public const S_PCT          = 7;
    public const S_CENTER       = 8;
    public const S_TEXT_Z       = 9;
    public const S_INT_Z        = 10;
    public const S_MONEY_Z      = 11;
    public const S_PCT_Z        = 12;
    public const S_CENTER_Z     = 13;
    public const S_KPI_LABEL    = 14;
    public const S_KPI_VALUE    = 15;
    public const S_KPI_MONEY    = 16;
    public const S_SECTION      = 17;
    public const S_TOTAL_LABEL  = 18;
    public const S_TOTAL_INT    = 19;
    public const S_TOTAL_MONEY  = 20;
    public const S_TOTAL_PCT    = 21;
    public const S_OK           = 22;
    public const S_BAD          = 23;
    public const S_RANK_TOP     = 24;
    public const S_FOOTER       = 25;
    public const S_KPI_PCT      = 26;
    public const S_BOLD_TEXT    = 27;
    public const S_BOLD_TEXT_Z  = 28;
    public const S_META_TOP     = 29;
    public const S_META_BOT     = 30;

    /** @var array<int, array<string, mixed>> */
    private array $sheets = [];
    private string $creator;

    public function __construct(string $creator = 'DENTIX')
    {
        $this->creator = $creator;
    }

    /**
     * Agrega una hoja y retorna su índice.
     * @param float[] $colWidths Ancho de columnas (en caracteres Excel)
     */
    public function addSheet(string $name, array $colWidths = []): int
    {
        $name = preg_replace('/[\\\\\/\?\*\[\]:]/u', ' ', $name) ?? 'Hoja';
        $name = function_exists('mb_substr') ? mb_substr($name, 0, 31, 'UTF-8') : substr($name, 0, 31);
        $this->sheets[] = [
            'name' => $name,
            'cols' => $colWidths,
            'rows' => [],
            'heights' => [],
            'merges' => [],
            'freeze' => null,
            'filter' => null,
            'tab' => null,
        ];
        return count($this->sheets) - 1;
    }

    /**
     * Agrega una fila. Cada celda puede ser escalar o ['v' => valor, 's' => estilo].
     * Retorna el número (1-based) de la fila agregada.
     */
    public function addRow(int $sheet, array $cells, ?float $height = null): int
    {
        $this->sheets[$sheet]['rows'][] = $cells;
        $rowNum = count($this->sheets[$sheet]['rows']);
        if ($height !== null) {
            $this->sheets[$sheet]['heights'][$rowNum] = $height;
        }
        return $rowNum;
    }

    public function currentRow(int $sheet): int
    {
        return count($this->sheets[$sheet]['rows']);
    }

    public function merge(int $sheet, string $range): void
    {
        $this->sheets[$sheet]['merges'][] = $range;
    }

    public function freeze(int $sheet, string $cell): void
    {
        $this->sheets[$sheet]['freeze'] = $cell;
    }

    public function autoFilter(int $sheet, string $range): void
    {
        $this->sheets[$sheet]['filter'] = $range;
    }

    public function tabColor(int $sheet, string $rgb): void
    {
        $this->sheets[$sheet]['tab'] = strtoupper(ltrim($rgb, '#'));
    }

    public static function col(int $index): string
    {
        $index++;
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }
        return $letters;
    }

    /** Genera el binario .xlsx completo. */
    public function build(): string
    {
        if ($this->sheets === []) {
            $this->addSheet('Hoja1');
        }

        $files = [];
        $files['[Content_Types].xml'] = $this->contentTypes();
        $files['_rels/.rels'] = $this->rootRels();
        $files['docProps/core.xml'] = $this->coreProps();
        $files['docProps/app.xml'] = $this->appProps();
        $files['xl/workbook.xml'] = $this->workbook();
        $files['xl/_rels/workbook.xml.rels'] = $this->workbookRels();
        $files['xl/styles.xml'] = $this->styles();
        foreach ($this->sheets as $i => $sheet) {
            $files['xl/worksheets/sheet' . ($i + 1) . '.xml'] = $this->sheetXml($sheet);
        }

        return $this->zip($files);
    }

    /** Envía el libro al navegador como descarga. */
    public function download(string $filename): void
    {
        $binary = $this->build();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($binary));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        echo $binary;
        exit;
    }

    // ------------------------------------------------------------------ XML parts

    private static function esc(string $v): string
    {
        $v = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $v) ?? '';
        return htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function contentTypes(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $_) {
            $sheets .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . $sheets
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function coreProps(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Reporte Gerencial DENTIX</dc:title>'
            . '<dc:creator>' . self::esc($this->creator) . '</dc:creator>'
            . '<cp:lastModifiedBy>' . self::esc($this->creator) . '</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function appProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>DENTIX</Application><Company>Clinica Odontologica DENTIX</Company>'
            . '</Properties>';
    }

    private function workbook(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $s) {
            $sheets .= '<sheet name="' . self::esc($s['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView activeTab="0"/></bookViews>'
            . '<sheets>' . $sheets . '</sheets>'
            . $this->definedNames()
            . '</workbook>';
    }

    private function definedNames(): string
    {
        $names = '';
        foreach ($this->sheets as $i => $s) {
            if ($s['filter'] === null) {
                continue;
            }
            [$a, $b] = explode(':', $s['filter']);
            $abs = static function (string $ref): string {
                return preg_match('/^([A-Z]+)(\d+)$/', $ref, $m) ? '$' . $m[1] . '$' . $m[2] : $ref;
            };
            $names .= '<definedName name="_xlnm._FilterDatabase" localSheetId="' . $i . '" hidden="1">'
                . "'" . self::esc(str_replace("'", "''", $s['name'])) . "'!" . $abs($a) . ':' . $abs($b) . '</definedName>';
        }
        return $names !== '' ? '<definedNames>' . $names . '</definedNames>' : '';
    }

    private function workbookRels(): string
    {
        $rels = '';
        $n = count($this->sheets);
        foreach ($this->sheets as $i => $_) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . ($n + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';
    }

    private function styles(): string
    {
        // Formatos numéricos personalizados
        $numFmts = '<numFmts count="2">'
            . '<numFmt numFmtId="164" formatCode="&quot;$&quot; #,##0"/>'
            . '<numFmt numFmtId="165" formatCode="0.0%"/>'
            . '</numFmts>';

        // Fuentes
        $fonts = [
            '<font><sz val="10"/><color rgb="FF1F2937"/><name val="Calibri"/><family val="2"/></font>',                 // 0 normal
            '<font><b/><sz val="18"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>',             // 1 title
            '<font><i/><sz val="10"/><color rgb="FF0F766E"/><name val="Calibri"/><family val="2"/></font>',             // 2 subtitle
            '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>',             // 3 header
            '<font><b/><sz val="10"/><color rgb="FF134E4A"/><name val="Calibri"/><family val="2"/></font>',             // 4 kpi label
            '<font><b/><sz val="14"/><color rgb="FF0D9488"/><name val="Calibri"/><family val="2"/></font>',             // 5 kpi value
            '<font><b/><sz val="13"/><color rgb="FF0F766E"/><name val="Calibri"/><family val="2"/></font>',             // 6 section
            '<font><b/><sz val="10"/><color rgb="FF065F46"/><name val="Calibri"/><family val="2"/></font>',             // 7 total / ok
            '<font><b/><sz val="10"/><color rgb="FFB91C1C"/><name val="Calibri"/><family val="2"/></font>',             // 8 bad
            '<font><b/><sz val="10"/><color rgb="FF92400E"/><name val="Calibri"/><family val="2"/></font>',             // 9 rank top
            '<font><i/><sz val="9"/><color rgb="FF6B7280"/><name val="Calibri"/><family val="2"/></font>',              // 10 footer
            '<font><b/><sz val="10"/><color rgb="FF1F2937"/><name val="Calibri"/><family val="2"/></font>',             // 11 bold text
        ];

        // Rellenos (0 y 1 son obligatorios por la especificación)
        $solid = static fn(string $rgb): string => '<fill><patternFill patternType="solid"><fgColor rgb="FF' . $rgb . '"/><bgColor indexed="64"/></patternFill></fill>';
        $fills = [
            '<fill><patternFill patternType="none"/></fill>',     // 0
            '<fill><patternFill patternType="gray125"/></fill>',  // 1
            $solid('0D9488'), // 2 title teal
            $solid('0F766E'), // 3 header dark teal
            $solid('F0FDFA'), // 4 zebra
            $solid('CCFBF1'), // 5 kpi label
            $solid('ECFDF5'), // 6 total / ok
            $solid('FEF2F2'), // 7 bad
            $solid('FEF3C7'), // 8 rank top
            $solid('FFFFFF'), // 9 white
        ];

        // Bordes
        $thin = 'FFB7E4DC';
        $borders = [
            '<border><left/><right/><top/><bottom/><diagonal/></border>', // 0
            '<border><left style="thin"><color rgb="' . $thin . '"/></left><right style="thin"><color rgb="' . $thin . '"/></right><top style="thin"><color rgb="' . $thin . '"/></top><bottom style="thin"><color rgb="' . $thin . '"/></bottom><diagonal/></border>', // 1 grid
            '<border><left/><right/><top/><bottom style="medium"><color rgb="FF0D9488"/></bottom><diagonal/></border>', // 2 section underline
            '<border><left style="thin"><color rgb="' . $thin . '"/></left><right style="thin"><color rgb="' . $thin . '"/></right><top style="medium"><color rgb="FF0D9488"/></top><bottom style="medium"><color rgb="FF0D9488"/></bottom><diagonal/></border>', // 3 total
        ];

        $al = static fn(string $h, bool $wrap = false, string $v = 'center'): string =>
            '<alignment horizontal="' . $h . '" vertical="' . $v . '"' . ($wrap ? ' wrapText="1"' : '') . ' indent="0"/>';
        $alIndent = static fn(string $h): string => '<alignment horizontal="' . $h . '" vertical="center" indent="1"/>';

        $xf = static function (int $numFmt, int $font, int $fill, int $border, string $align) : string {
            return '<xf numFmtId="' . $numFmt . '" fontId="' . $font . '" fillId="' . $fill . '" borderId="' . $border . '" xfId="0"'
                . ($numFmt ? ' applyNumberFormat="1"' : '') . ' applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">' . $align . '</xf>';
        };

        $cellXfs = [
            self::S_DEFAULT     => '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>',
            self::S_TITLE       => $xf(0, 1, 2, 0, $alIndent('left')),
            self::S_SUBTITLE    => $xf(0, 2, 0, 0, $alIndent('left')),
            self::S_HEADER      => $xf(0, 3, 3, 1, $al('center', true)),
            self::S_TEXT        => $xf(0, 0, 9, 1, $alIndent('left')),
            self::S_INT         => $xf(3, 0, 9, 1, $al('center')),
            self::S_MONEY       => $xf(164, 0, 9, 1, $alIndent('right')),
            self::S_PCT         => $xf(165, 0, 9, 1, $al('center')),
            self::S_CENTER      => $xf(0, 0, 9, 1, $al('center')),
            self::S_TEXT_Z      => $xf(0, 0, 4, 1, $alIndent('left')),
            self::S_INT_Z       => $xf(3, 0, 4, 1, $al('center')),
            self::S_MONEY_Z     => $xf(164, 0, 4, 1, $alIndent('right')),
            self::S_PCT_Z       => $xf(165, 0, 4, 1, $al('center')),
            self::S_CENTER_Z    => $xf(0, 0, 4, 1, $al('center')),
            self::S_KPI_LABEL   => $xf(0, 4, 5, 1, $alIndent('left')),
            self::S_KPI_VALUE   => $xf(3, 5, 9, 1, $al('center')),
            self::S_KPI_MONEY   => $xf(164, 5, 9, 1, $al('center')),
            self::S_SECTION     => $xf(0, 6, 0, 2, $al('left', false, 'bottom')),
            self::S_TOTAL_LABEL => $xf(0, 7, 6, 3, $alIndent('left')),
            self::S_TOTAL_INT   => $xf(3, 7, 6, 3, $al('center')),
            self::S_TOTAL_MONEY => $xf(164, 7, 6, 3, $alIndent('right')),
            self::S_TOTAL_PCT   => $xf(165, 7, 6, 3, $al('center')),
            self::S_OK          => $xf(0, 7, 6, 1, $al('center')),
            self::S_BAD         => $xf(0, 8, 7, 1, $al('center')),
            self::S_RANK_TOP    => $xf(0, 9, 8, 1, $al('center')),
            self::S_FOOTER      => $xf(0, 10, 0, 0, $alIndent('left')),
            self::S_KPI_PCT     => $xf(165, 5, 9, 1, $al('center')),
            self::S_BOLD_TEXT   => $xf(0, 11, 9, 1, $alIndent('left')),
            self::S_BOLD_TEXT_Z => $xf(0, 11, 4, 1, $alIndent('left')),
            self::S_META_TOP    => $xf(0, 2, 4, 0, $alIndent('left')),
            self::S_META_BOT    => $xf(0, 2, 4, 2, $alIndent('left')),
        ];
        ksort($cellXfs);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $numFmts
            . '<fonts count="' . count($fonts) . '">' . implode('', $fonts) . '</fonts>'
            . '<fills count="' . count($fills) . '">' . implode('', $fills) . '</fills>'
            . '<borders count="' . count($borders) . '">' . implode('', $borders) . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="' . count($cellXfs) . '">' . implode('', $cellXfs) . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function sheetXml(array $s): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';

        $xml .= '<sheetPr>' . ($s['tab'] ? '<tabColor rgb="FF' . $s['tab'] . '"/>' : '') . '<pageSetUpPr fitToPage="1"/></sheetPr>';

        $xml .= '<sheetViews><sheetView workbookViewId="0" showGridLines="0" zoomScale="100">';
        if ($s['freeze'] !== null && preg_match('/^([A-Z]+)(\d+)$/', $s['freeze'], $m)) {
            $ySplit = (int) $m[2] - 1;
            if ($ySplit > 0) {
                $xml .= '<pane ySplit="' . $ySplit . '" topLeftCell="' . $s['freeze'] . '" activePane="bottomLeft" state="frozen"/>'
                    . '<selection pane="bottomLeft" activeCell="' . $s['freeze'] . '" sqref="' . $s['freeze'] . '"/>';
            }
        }
        $xml .= '</sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="18" customHeight="1"/>';

        if ($s['cols'] !== []) {
            $xml .= '<cols>';
            foreach ($s['cols'] as $i => $w) {
                $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $w . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        foreach ($s['rows'] as $r => $cells) {
            $rowNum = $r + 1;
            $attrs = ' r="' . $rowNum . '"';
            if (isset($s['heights'][$rowNum])) {
                $attrs .= ' ht="' . $s['heights'][$rowNum] . '" customHeight="1"';
            }
            $xml .= '<row' . $attrs . '>';
            foreach (array_values($cells) as $c => $cell) {
                $ref = self::col($c) . $rowNum;
                $value = is_array($cell) ? ($cell['v'] ?? null) : $cell;
                $style = is_array($cell) ? (int) ($cell['s'] ?? 0) : 0;
                $sAttr = $style > 0 ? ' s="' . $style . '"' : '';

                if ($value === null || $value === '') {
                    $xml .= '<c r="' . $ref . '"' . $sAttr . '/>';
                } elseif (is_int($value) || is_float($value)) {
                    $num = is_float($value) ? rtrim(rtrim(sprintf('%.10F', $value), '0'), '.') : (string) $value;
                    $xml .= '<c r="' . $ref . '"' . $sAttr . '><v>' . ($num === '' || $num === '-' ? '0' : $num) . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '"' . $sAttr . ' t="inlineStr"><is><t xml:space="preserve">' . self::esc((string) $value) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if ($s['filter'] !== null) {
            $xml .= '<autoFilter ref="' . $s['filter'] . '"/>';
        }
        if ($s['merges'] !== []) {
            $xml .= '<mergeCells count="' . count($s['merges']) . '">';
            foreach ($s['merges'] as $mRange) {
                $xml .= '<mergeCell ref="' . $mRange . '"/>';
            }
            $xml .= '</mergeCells>';
        }

        $xml .= '<pageMargins left="0.4" right="0.4" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>';
        $xml .= '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>';
        $xml .= '<headerFooter><oddFooter>&amp;L&amp;8DENTIX - Reporte Gerencial&amp;R&amp;8Pagina &amp;P de &amp;N</oddFooter></headerFooter>';
        $xml .= '</worksheet>';
        return $xml;
    }

    // ------------------------------------------------------------------ ZIP (sin dependencias)

    private function zip(array $files): string
    {
        $data = '';
        $central = '';
        $offset = 0;
        $time = getdate();
        $dosTime = (($time['hours'] & 0x1F) << 11) | (($time['minutes'] & 0x3F) << 5) | ((int) ($time['seconds'] / 2) & 0x1F);
        $dosDate = ((($time['year'] - 1980) & 0x7F) << 9) | (($time['mon'] & 0x0F) << 5) | ($time['mday'] & 0x1F);
        $canDeflate = function_exists('gzdeflate');

        foreach ($files as $name => $content) {
            $crc = crc32($content);
            $size = strlen($content);
            $method = 0;
            $payload = $content;
            if ($canDeflate) {
                $deflated = gzdeflate($content, 6);
                if ($deflated !== false) {
                    $method = 8;
                    $payload = $deflated;
                }
            }
            $csize = strlen($payload);
            $nameLen = strlen($name);

            $local = pack('VvvvvvVVVvv', 0x04034b50, 20, 0x0800, $method, $dosTime, $dosDate, $crc, $csize, $size, $nameLen, 0) . $name;
            $data .= $local . $payload;

            $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0x0800, $method, $dosTime, $dosDate, $crc, $csize, $size, $nameLen, 0, 0, 0, 0, 0, $offset) . $name;
            $offset += strlen($local) + $csize;
        }

        $count = count($files);
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, strlen($central), $offset, 0);
        return $data . $central . $end;
    }
}
