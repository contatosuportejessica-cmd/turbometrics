// Anti-ansiedad — PWA Install Helper
// O template HTML ja tem beforeinstallprompt e installPWA() inline.
// Este arquivo serve como fallback e para iOS.
(function(){
    function isiPhone(){return /iPhone|iPad|iPod/.test(navigator.userAgent)}
    function isInstalled(){return window.matchMedia("(display-mode: standalone)").matches||window.navigator.standalone===true}

    if(isInstalled()) return; // ja instalado, nao precisa

    // iOS: mostrar instrucoes
    if(isiPhone()){
        setTimeout(function(){
            if(document.getElementById("iphoneInstallBtn")) return;
            var b=document.createElement("button");
            b.id="iphoneInstallBtn";
            b.textContent="INSTALAR NO IPHONE";
            b.style.cssText="position:fixed;bottom:100px;right:15px;background:linear-gradient(135deg,#ff6b35,#ff8b35);color:white;border:none;padding:12px 16px;border-radius:25px;font-weight:bold;font-size:14px;z-index:9999;box-shadow:0 4px 15px rgba(255,107,53,0.4);cursor:pointer;";
            b.onclick=function(){
                alert("NO IPHONE/IPAD:\n\n1. Toque em COMPARTILHAR (icone de setinha)\n2. ADICIONAR A TELA DE INICIO\n3. Toque ADICIONAR");
                this.remove();
            };
            document.body.appendChild(b);
            setTimeout(function(){var x=document.getElementById("iphoneInstallBtn");if(x)x.remove()},30000);
        },3000);
    }
})();
