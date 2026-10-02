<?php
/**
 * Anti-ansiedad — Powered by EvaFunnels
 * NAO EDITAR — alteracoes serao sobrescritas.
 */

// IMPORTANTE: no-cache em TODAS as respostas (inclusive erros 403/503)
// Sem isso, o LiteSpeed do Hostinger e o browser cacheiam a pagina de
// "Dominio nao autorizado" mesmo depois do cliente registrar o dominio.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$AFP_LICENSE_KEY = '8cd49ca834859712705c07e4b0103705';
$AFP_APP_SLUG    = 'anti_ansiedade_es';
$AFP_API_URL     = 'https://eva.igorstorm.com/api/render.php';

$cacheFile = __DIR__ . '/.afp_cache_' . $AFP_APP_SLUG . '.html';
$cacheTTL  = 60; // 1 min — gap maximo de revogacao apos suspensao/refund/troca-de-dominio

// Limpar cache via URL: ?afp_clear=1
if (isset($_GET['afp_clear'])) {
    foreach (glob(__DIR__ . '/.afp_cache_*.html') as $f) @unlink($f);
    header('Location: ./');
    exit;
}

/* Todos os apps deste site compartilham esta pasta _router, entao os caches
   ficam lado a lado. O laco antigo apagava o cache de TODOS os outros apps a
   cada visita: so o ultimo aberto tinha copia local, e o fallback logo abaixo
   ("plataforma fora do ar -> serve o cache") nao protegia o resto do catalogo.
   Cada app passa a guardar o seu; sai apenas o formato legado e o lixo com
   mais de 7 dias. (29/08/2026) */
@unlink(__DIR__ . '/.afp_cache.html');
foreach (glob(__DIR__ . '/.afp_cache_*.html') as $old) {
    if ($old !== $cacheFile && (time() - @filemtime($old)) > 604800) @unlink($old);
}

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    readfile($cacheFile);
    exit;
}

$domain = $_SERVER['HTTP_HOST'] ?? '';
$url = $AFP_API_URL . '?' . http_build_query([
    'key'  => $AFP_LICENSE_KEY,
    'slug' => $AFP_APP_SLUG,
    'd'    => $domain
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 403) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate"><meta http-equiv="Pragma" content="no-cache"><meta http-equiv="Expires" content="0"><title>Acesso Negado</title></head><body style="display:flex;align-items:center;justify-content:center;height:100vh;background:#0f0f1a;color:#ff6b35;font-family:sans-serif;text-align:center;padding:20px;margin:0"><div style="max-width:520px"><h1 style="font-size:1.6rem;margin-bottom:14px">Dominio nao autorizado</h1><p style="color:#bbb;line-height:1.6;margin-bottom:18px">Este dominio (' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? '', ENT_QUOTES) . ') nao esta autorizado a exibir este app.</p><div style="background:rgba(255,255,255,0.05);border-radius:10px;padding:14px;text-align:left;font-size:0.9rem;color:#ccc;margin-bottom:18px"><b style="color:#fff">Como resolver:</b><br>1. Acesse <a href="https://eva.igorstorm.com/painel" style="color:#ff6b35" target="_blank">eva.igorstorm.com/painel</a><br>2. Em "Dominio Registrado", coloque <b style="color:#fff">' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? '', ENT_QUOTES) . '</b><br>3. Aguarde alguns segundos e <a href="?afp_clear=1" style="color:#ff6b35">recarregue esta pagina</a></div><p style="color:#666;font-size:0.8rem">Se ja registrou, clique em <a href="?afp_clear=1" style="color:#ff6b35">recarregar</a>.</p></div></body></html>';
} elseif ($html && strlen($html) > 500) {
    // Aplicar config override do cliente (se existir)
    $overrideFile = __DIR__ . '/config-override.json';
    if (file_exists($overrideFile)) {
        $overrideJSON = file_get_contents($overrideFile);
        if ($overrideJSON && strlen($overrideJSON) > 10) {
            $marker = '<script>const APP_CONFIG = ';
            $pos1 = strpos($html, $marker);
            if ($pos1 !== false) {
                $pos2 = strpos($html, ';</script>', $pos1);
                if ($pos2 !== false) {
                    $html = substr($html, 0, $pos1) . $marker . $overrideJSON . ';</script>' . substr($html, $pos2 + 10);
                }
            }
        }
    }
    @file_put_contents($cacheFile, $html);
    echo $html;
} elseif (file_exists($cacheFile)) {
    readfile($cacheFile);
} else {
    http_response_code(503);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate"><title>Servico indisponivel</title></head><body style="display:flex;align-items:center;justify-content:center;height:100vh;background:#0f0f1a;color:#ff6b35;font-family:sans-serif;text-align:center;padding:20px;margin:0"><div style="max-width:520px"><h1>Servico indisponivel</h1><p style="color:#bbb">Nao foi possivel carregar o app neste momento. Tente <a href="?afp_clear=1" style="color:#ff6b35">recarregar</a> em alguns segundos.</p></div></body></html>';
}
