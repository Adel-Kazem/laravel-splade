<?php

namespace ProtoneMedia\Splade\Http;

use Illuminate\Support\Facades\Blade;
use ProtoneMedia\Splade\Components\Cell;
use ProtoneMedia\Splade\Table\Column;

class BladeDirectives
{
    /**
     * Registers the Blade @splade, @spladeHead, and @cell directives.
     *
     * @return void
     */
    public function registerHandlers()
    {
        Blade::directive('splade', [$this, 'splade']);
        Blade::directive('spladeHead', [$this, 'spladeHead']);
        Blade::directive('preserveScroll', [$this, 'preserveScroll']);
        Blade::directive('spladeNavTab', [$this, 'spladeNavTab']);

        $this->registerSEODirectives();
        $this->registerTableCellDirective();
    }

    /**
     * Returns a template with the main app element and all required Splade attributes.
     *
     * @param  string  $expression
     */
    public function splade($expression = ''): string
    {
        $id = trim(trim($expression), "\'\"") ?: 'app';

        $template[] = '<div id="' . $id . '" data-components="{{ json_encode($components) }}" data-html="{{ json_encode($html) }}" data-dynamics="{{ json_encode($dynamics) }}" data-splade="{{ json_encode($splade) }}">';
        $template[] = '{!! $ssrBody !!}';
        $template[] = '</div>';

        return implode(PHP_EOL, $template);
    }

    /**
     * Returns a template that eventually renders the Splade SEO Head.
     */
    public function spladeHead(): string
    {
        return '{{ app(\'laravel-splade-seo\')->renderHead() }}';
    }

    /**
     * Inline script (put it in <head>, before the app bundle) that gives this
     * browser tab a stable id (sessionStorage) and sends it as X-Splade-Tab on
     * every same-origin XHR, so the header "Up" arrow keeps history PER TAB.
     * No JS rebuild needed. See ProtoneMedia\Splade\Navigation\UpNavigator.
     */
    public function spladeNavTab(): string
    {
        return <<<'HTML'
<script>(function(){var k="splade_tab_id",id;function g(){return(window.crypto&&crypto.randomUUID)?crypto.randomUUID():"t"+Date.now().toString(36)+Math.random().toString(36).slice(2,10)}try{id=sessionStorage.getItem(k);if(!id){id=g();sessionStorage.setItem(k,id)}}catch(e){id=g()}window.spladeTabId=id;var P=XMLHttpRequest.prototype,o=P.open,s=P.send;P.open=function(m,u){try{this.__spladeSame=new URL(u,location.href).origin===location.origin}catch(e){this.__spladeSame=false}return o.apply(this,arguments)};P.send=function(){if(this.__spladeSame){try{this.setRequestHeader("X-Splade-Tab",id)}catch(e){}}return s.apply(this,arguments)}})();</script>
HTML;
    }

    /**
     * Returns a Vue directive to preserve the scroll props of the element.
     */
    public function preserveScroll($expression): string
    {
        $key = md5(trim(trim($expression), "\'\""));

        return "v-SpladePreserveScroll:{$key}";
    }

    /**
     * Parses the expression given to the Table Cell directive. It returns
     * an array containing the name of the cell, and the callback
     * function that's used to render the contents of the cell.
     */
    public static function parseTableCellDirectiveExpression(string $expression): array
    {
        preg_match("/('|\")([\w\-_\.]+)('|\")(,)?(\s*)(.*)/", $expression, $matches);

        $name = trim($matches[2] ?? '');

        $arguments = trim($matches[6] ?? '', '\[\]');

        $splitted = preg_split('/\],(\s*)/', $arguments);

        $slotArguments = trim($splitted[0] ?? '');
        $slotUses      = trim(ltrim($splitted[1] ?? '', '['));

        $slotUses = $slotUses ? "\$__env, {$slotUses}" : '$__env';

        $function = "function ({$slotArguments})";
        $function .= " use ({$slotUses})";

        return [$name, $function];
    }

    /**
     * Registers the Blade SEO directives.
     *
     * @return void
     */
    public function registerSEODirectives()
    {
        $title       = config('splade.blade.seo_title_directive', 'seoTitle');
        $description = config('splade.blade.seo_description_directive', 'seoDescription');
        $keywords    = config('splade.blade.seo_keywords_directive', 'seoKeywords');

        if ($title !== false) {
            Blade::directive($title, function ($expression) {
                return "<?php \ProtoneMedia\Splade\Facades\SEO::title($expression); ?>";
            });
        }

        if ($description !== false) {
            Blade::directive($description, function ($expression) {
                return "<?php \ProtoneMedia\Splade\Facades\SEO::description($expression); ?>";
            });
        }

        if ($keywords !== false) {
            Blade::directive($keywords, function ($expression) {
                return "<?php \ProtoneMedia\Splade\Facades\SEO::keywords($expression); ?>";
            });
        }
    }

    /**
     * Registers the Blade @cell directive.
     *
     * @return void
     */
    public function registerTableCellDirective()
    {
        $cellDirectiveName = config('splade.blade.table_cell_directive');

        Blade::directive($cellDirectiveName, function ($expression) {
            [$name, $function] = BladeDirectives::parseTableCellDirectiveExpression($expression);

            $name = Column::hashKey($name);

            return "<?php \$__env->slot('spladeTableCell{$name}', {$function} { ?>";
        });

        Blade::directive('end' . $cellDirectiveName, function () {
            return '<?php }); ?>';
        });
    }
}
