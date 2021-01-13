<?php
declare(strict_types=1);

use FFI;
use FFI\CData;

class PDFLibWrapper
{
    private FFI $ffi;
    private CData $pdf;

    public function __construct()
    {
        $cdefs = file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__);

        $this->ffi = FFI::cdef($cdefs, 'pdf.so');

        $this->pdf = $this->ffi->PDF_new2(
            fn($p, $errortype, $msg) => trigger_error($msg, E_USER_ERROR),
            null,
            null,
            null,
            null
        );
    }

    /**
     * Internal method for fixing the difference between C and PHP:
     * -1 error in C and 0 error in PHP
     * other values in PHP = C - 1
     *
     * @param float|int $result
     * @return float|int
     */
    private static function encodeResult($result)
    {
        return $result === -1 ? 0 : -($result + 1);
    }

    /**
     * Internal method for fixing the difference between C and PHP:
     * -1 error in C and 0 error in PHP
     * other values in PHP = C - 1
     *
     * @param float|int $result
     * @return float|int
     */
    private static function decodeResult($result)
    {
        return $result < 0 ? -($result + 1) : $result;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->ffi->{"PDF_$name"}($this->pdf, ...$args);
    }

    public function begin_document(string $filename, string $optlist): int
    {
        return $this->ffi->PDF_begin_document($this->pdf, $filename, strlen($filename), $optlist);
    }

    public function load_font(string $fontname, string $encoding, string $optlist): int
    {
        $ret = $this->ffi->PDF_load_font($this->pdf, $fontname, strlen($fontname), $encoding, $optlist);
        return self::encodeResult($ret);
    }

    public function create_bookmark(string $text, string $optlist): int
    {
        return $this->ffi->PDF_create_bookmark($this->pdf, $text, strlen($text), $optlist);
    }

    public function load_image(string $imagetype, string $filename, string $optlist): int
    {
        $ret = $this->ffi->PDF_load_image($this->pdf, $imagetype, $filename, strlen($filename), $optlist);
        return self::encodeResult($ret);
    }

    public function fit_textline(string $text, float $x, float $y, string $optlist): void
    {
        $this->ffi->PDF_fit_textline($this->pdf, $text, strlen($text), $x, $y, $optlist);
    }

    public function get_value(string $key, float $modifier): float
    {
        return $this->ffi->PDF_get_value($this->pdf, $key, self::decodeResult($modifier));
    }

    public function setfont(int $font, float $fontsize): void
    {
        $this->ffi->PDF_setfont($this->pdf, self::decodeResult($font), $fontsize);
    }

    public function begin_page_ext(float $width, float $height, string $optlist): bool
    {
        $this->ffi->PDF_begin_page_ext($this->pdf, $width, $height, $optlist);
        return true;
    }

    public function set_parameter(string $key, string $value): bool
    {
        $this->ffi->PDF_set_parameter($this->pdf, $key, $value);
        return true;
    }

    public function fit_image(int $image, float $x, float $y, string $optlist): bool
    {
        $this->ffi->PDF_fit_image($this->pdf, self::decodeResult($image), $x, $y, $optlist);
        return true;
    }

    public function stringwidth(string $text, int $font, float $fontsize): float
    {
        return $this->ffi->PDF_stringwidth($this->pdf, $text, self::decodeResult($font), $fontsize);
    }
}

// Cdefs for FFI
__halt_compiler();

typedef void PDF;
typedef struct PDFlib_api_s PDFlib_api;
typedef size_t (*writeproc_t)(PDF *p1, void *data, size_t size);

typedef void  (*errorproc_t)(PDF *p1, int errortype, const char *msg);
typedef void* (*allocproc_t)(PDF *p2, size_t size, const char *caller);
typedef void* (*reallocproc_t)(PDF *p3, void *mem, size_t size, const char *caller);
typedef void  (*freeproc_t)(PDF *p4, void *mem);

void                  PDF_activate_item(const PDF *p, int id);
int                   PDF_add_bookmark(PDF *p, const char *text, int parent, int open);
void                  PDF_add_launchlink(PDF *p, double llx, double lly, double urx, double ury, const char *filename);
void                  PDF_add_locallink(PDF *p, double llx, double lly, double urx, double ury, int page, const char *optlist);
void                  PDF_add_nameddest(PDF *p, const char *name, int len, const char *optlist);
void                  PDF_add_note(PDF *p, double llx, double lly, double urx, double ury, const char *contents, const char *title, const char *icon, int open);
void                  PDF_add_pdflink(PDF *p, double llx, double lly, double urx, double ury, const char *filename, int page, const char *optlist);
int                   PDF_add_table_cell(PDF *p, int table, int column, int row, const char *text, int len, const char *optlist);
int                   PDF_add_textflow(PDF *p, int textflow, const char *text, int len, const char *optlist);
void                  PDF_add_thumbnail(PDF *p, int image);
void                  PDF_add_weblink(PDF *p, double llx, double lly, double urx, double ury, const char *url);
void                  PDF_arc(PDF *p, double x, double y, double r, double alpha, double beta);
void                  PDF_arcn(PDF *p, double x, double y, double r, double alpha, double beta);
void                  PDF_attach_file(PDF *p, double llx, double lly, double urx, double ury, const char *filename, const char *description, const char *author, const char *mimetype, const char *icon);
int                   PDF_begin_document(PDF *p, const char *filename, int len, const char *optlist);
void                  PDF_begin_font(PDF *p, const char *fontname, int len, double a, double b, double c, double d, double e, double f, const char *optlist);
void                  PDF_begin_glyph(PDF *p, const char *glyphname, double wx, double llx, double lly, double urx, double ury);
int                   PDF_begin_item(PDF *p, const char *tagname, const char *optlist);
void                  PDF_begin_layer(PDF *p, int layer);
void                  PDF_begin_mc(PDF *p, const char *tagname, const char *optlist);
void                  PDF_begin_page(PDF *p, double width, double height);
void                  PDF_begin_page_ext(PDF *p, double width, double height, const char *optlist);
int                   PDF_begin_pattern(PDF *p, double width, double height, double xstep, double ystep, int painttype);
int                   PDF_begin_template(PDF *p, double width, double height);
int                   PDF_begin_template_ext(PDF *p, double width, double height, const char *optlist);
void                  PDF_circle(PDF *p, double x, double y, double r);
void                  PDF_clip(PDF *p);
void                  PDF_close(PDF *p);
void                  PDF_close_image(PDF *p, int image);
void                  PDF_close_pdi(PDF *p, int doc);
void                  PDF_close_pdi_document(PDF *p, int doc);
void                  PDF_close_pdi_page(PDF *p, int page);
void                  PDF_closepath(PDF *p);
void                  PDF_closepath_fill_stroke(PDF *p);
void                  PDF_closepath_stroke(PDF *p);
void                  PDF_concat(PDF *p, double a, double b, double c, double d, double e, double f);
void                  PDF_continue_text(PDF *p, const char *text);
int                   PDF_create_3dview(PDF *p, const char *username, int len, const char *optlist);
int                   PDF_create_action(PDF *p, const char *type, const char *optlist);
void                  PDF_create_annotation(PDF *p, double llx, double lly, double urx, double ury, const char *type, const char *optlist);
int                   PDF_create_bookmark(PDF *p, const char *text, int len, const char *optlist);
void                  PDF_create_field(PDF *p, double llx, double lly, double urx, double ury, const char *name, int len, const char *type, const char *optlist);
void                  PDF_create_fieldgroup(PDF *p, const char *name, int len, const char *optlist);
int                   PDF_create_gstate(PDF *p, const char *optlist);
void                  PDF_create_pvf(PDF *p, const char *filename, int len, const void *data, size_t size, const char *optlist);
int                   PDF_create_textflow(PDF *p, const char *text, int len, const char *optlist);
void                  PDF_curveto(PDF *p, double x1, double y1, double x2, double y2, double x3, double y3);
int                   PDF_define_layer(PDF *p, const char *name, int len, const char *optlist);
void                  PDF_delete(PDF *p);
int                   PDF_delete_pvf(PDF *p, const char *filename, int len);
void                  PDF_delete_table(PDF *p, int table, const char *optlist);
void                  PDF_delete_textflow(PDF *p, int textflow);
void                  PDF_encoding_set_char(PDF *p, const char *encoding, int slot, const char *glyphname, int uv);
void                  PDF_end_document(PDF *p, const char *optlist);
void                  PDF_end_font(PDF *p);
void                  PDF_end_glyph(PDF *p);
void                  PDF_end_item(PDF *p, int id);
void                  PDF_end_layer(PDF *p);
void                  PDF_end_mc(PDF *p);
void                  PDF_end_page(PDF *p);
void                  PDF_end_page_ext(PDF *p, const char *optlist);
void                  PDF_end_pattern(PDF *p);
void                  PDF_end_template(PDF *p);
void                  PDF_endpath(PDF *p);
void                  PDF_fill(PDF *p);
int                   PDF_fill_imageblock(PDF *p, int page, const char *blockname, int image, const char *optlist);
int                   PDF_fill_pdfblock(PDF *p, int page, const char *blockname, int contents, const char *optlist);
void                  PDF_fill_stroke(PDF *p);
int                   PDF_fill_textblock(PDF *p, int page, const char *blockname, const char *text, int len, const char *optlist);
int                   PDF_findfont(PDF *p, const char *fontname, const char *encoding, int embed);
void                  PDF_fit_image(PDF *p, int image, double x, double y, const char *optlist);
void                  PDF_fit_pdi_page(PDF *p, int page, double x, double y, const char *optlist);
const char *          PDF_fit_table(PDF *p, int table, double llx, double lly, double urx, double ury, const char *optlist);
const char *          PDF_fit_textflow(PDF *p, int textflow, double llx, double lly, double urx, double ury, const char *optlist);
void                  PDF_fit_textline(PDF *p, const char *text, int len, double x, double y, const char *optlist);
const char *          PDF_get_apiname(PDF *p);
const char *          PDF_get_buffer(PDF *p, long *size);
const char *          PDF_get_errmsg(PDF *p);
int                   PDF_get_errnum(PDF *p);
const char *          PDF_get_parameter(PDF *p, const char *key, double modifier);
const char *          PDF_get_pdi_parameter(PDF *p, const char *key, int doc, int page, int reserved, int *len);
double                PDF_get_pdi_value(PDF *p, const char *key, int doc, int page, int reserved);
double                PDF_get_value(PDF *p, const char *key, double modifier);
double                PDF_info_font(PDF *p, int font, const char *keyword, const char *optlist);
double                PDF_info_matchbox(PDF *p, const char *boxname, int len, int num, const char *keyword);
double                PDF_info_table(PDF *p, int table, const char *keyword);
double                PDF_info_textflow(PDF *p, int textflow, const char *keyword);
double                PDF_info_textline(PDF *p, const char *text, int len, const char *keyword, const char *optlist);
void                  PDF_initgraphics(PDF *p);
void                  PDF_lineto(PDF *p, double x, double y);
int                   PDF_load_3ddata(PDF *p, const char *filename, int len, const char *optlist);
int                   PDF_load_font(PDF *p, const char *fontname, int len, const char *encoding, const char *optlist);
int                   PDF_load_iccprofile(PDF *p, const char *profilename, int len, const char *optlist);
int                   PDF_load_image(PDF *p, const char *imagetype, const char *filename, int len, const char *optlist);
int                   PDF_makespotcolor(PDF *p, const char *spotname, int reserved);
void                  PDF_mc_point(PDF *p, const char *tagname, const char *optlist);
void                  PDF_moveto(PDF *p, double x, double y);
PDF *                 PDF_new(void);
PDF *                 PDF_new2(errorproc_t errorhandler, allocproc_t allocproc, reallocproc_t reallocproc, freeproc_t freeproc, void *opaque);
int                   PDF_open_CCITT(PDF *p, const char *filename, int width, int height, int BitReverse, int K, int BlackIs1);
int                   PDF_open_file(PDF *p, const char *filename);
int                   PDF_open_image(PDF *p, const char *imagetype, const char *source, const char *data, long length, int width, int height, int components, int bpc, const char *params);
int                   PDF_open_image_file(PDF *p, const char *imagetype, const char *filename, const char *stringparam, int intparam);
int                   PDF_open_pdi(PDF *p, const char *filename, const char *optlist, int len);
int                   PDF_open_pdi_document(PDF *p, const char *filename, int len, const char *optlist);
int                   PDF_open_pdi_page(PDF *p, int doc, int pagenumber, const char *optlist);
double                PDF_pcos_get_number(PDF *p, int doc, const char *path, ...);
const char *          PDF_pcos_get_string(PDF *p, int doc, const char *path, ...);
const unsigned char * PDF_pcos_get_stream(PDF *p, int doc, int *length, const char *optlist, const char *path, ...);
void                  PDF_place_image(PDF *p, int image, double x, double y, double scale);
void                  PDF_place_pdi_page(PDF *p, int page, double x, double y, double sx, double sy);
int                   PDF_process_pdi(PDF *p, int doc, int page, const char *optlist);
void                  PDF_rect(PDF *p, double x, double y, double width, double height);
void                  PDF_restore(PDF *p);
void                  PDF_resume_page(PDF *p, const char *optlist);
void                  PDF_rotate(PDF *p, double phi);
void                  PDF_save(PDF *p);
void                  PDF_scale(PDF *p, double sx, double sy);
void                  PDF_set_border_color(PDF *p, double red, double green, double blue);
void                  PDF_set_border_dash(PDF *p, double b, double w);
void                  PDF_set_border_style(PDF *p, const char *style, double width);
void                  PDF_set_gstate(PDF *p, int gstate);
void                  PDF_set_info(PDF *p, const char *key, const char *value);
void                  PDF_set_layer_dependency(PDF *p, const char *type, const char *optlist);
void                  PDF_set_parameter(PDF *p, const char *key, const char *value);
void                  PDF_set_text_pos(PDF *p, double x, double y);
void                  PDF_set_value(PDF *p, const char *key, double value);
void                  PDF_setcolor(PDF *p, const char *fstype, const char *colorspace, double c1, double c2, double c3, double c4);
void                  PDF_setdash(PDF *p, double b, double w);
void                  PDF_setdashpattern(PDF *p, const char *optlist);
void                  PDF_setflat(PDF *p, double flatness);
void                  PDF_setfont(PDF *p, int font, double fontsize);
void                  PDF_setgray(PDF *p, double gray);
void                  PDF_setgray_fill(PDF *p, double gray);
void                  PDF_setgray_stroke(PDF *p, double gray);
void                  PDF_setlinecap(PDF *p, int linecap);
void                  PDF_setlinejoin(PDF *p, int linejoin);
void                  PDF_setlinewidth(PDF *p, double width);
void                  PDF_setmatrix(PDF *p, double a, double b, double c, double d, double e, double f);
void                  PDF_setmiterlimit(PDF *p, double miter);
void                  PDF_setpolydash(PDF *p, float *dasharray, int length);
void                  PDF_setrgbcolor(PDF *p, double red, double green, double blue);
void                  PDF_setrgbcolor_fill(PDF *p, double red, double green, double blue);
void                  PDF_setrgbcolor_stroke(PDF *p, double red, double green, double blue);
int                   PDF_shading(PDF *p, const char *shtype, double x0, double y0, double x1, double y1, double c1, double c2, double c3, double c4, const char *optlist);
int                   PDF_shading_pattern(PDF *p, int shading, const char *optlist);
void                  PDF_shfill(PDF *p, int shading);
void                  PDF_show(PDF *p, const char *text);
int                   PDF_show_boxed(PDF *p, const char *text, double left, double top, double width, double height, const char *hmode, const char *feature);
void                  PDF_show_xy(PDF *p, const char *text, double x, double y);
void                  PDF_skew(PDF *p, double alpha, double beta);
double                PDF_stringwidth(PDF *p, const char *text, int font, double fontsize);
void                  PDF_stroke(PDF *p);
void                  PDF_suspend_page(PDF *p, const char *optlist);
void                  PDF_translate(PDF *p, double tx, double ty);
const char *          PDF_utf16_to_utf8(PDF *p, const char *utf16string, int len, int *size);
const char *          PDF_utf32_to_utf16(PDF *p, const char *utf32string, int len, const char *ordering, int *size);
