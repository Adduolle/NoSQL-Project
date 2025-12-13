<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* waitroom.twig */
class __TwigTemplate_37099d9d12fea269f54cee8c6422cfc1 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "waitroom.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"UTF-8\">
    <title>Waitroom</title>
    <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
</head>
<body>
    <h1>Waiting...</h1>
    ";
        // line 10
        if (array_key_exists("start_button", $context)) {
            // line 11
            yield "        <button href=\"";
            yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("start_game");
            yield "\">Lancer la partie</button>
    ";
        }
        // line 13
        yield "
    <h2>Joueurs présents :</h2>
    <ul id=\"players-list\">
        <li>Chargement…</li>
    </ul>

    <div id=\"host-actions\" style=\"display:none;\">
        <em>Vous êtes l'host de la partie</em>
        <form method=\"post\" action=\"";
        // line 21
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("start_game", ["roomId" => (isset($context["roomID"]) || array_key_exists("roomID", $context) ? $context["roomID"] : (function () { throw new RuntimeError('Variable "roomID" does not exist.', 21, $this->source); })())]), "html", null, true);
        yield "\">
            <button type=\"submit\">Start Game</button>
        </form>
    </div>

    <script>
        const roomId = ";
        // line 27
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["roomID"]) || array_key_exists("roomID", $context) ? $context["roomID"] : (function () { throw new RuntimeError('Variable "roomID" does not exist.', 27, $this->source); })()), "html", null, true);
        yield ";
        const userId = ";
        // line 28
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userID"]) || array_key_exists("userID", $context) ? $context["userID"] : (function () { throw new RuntimeError('Variable "userID" does not exist.', 28, $this->source); })()), "html", null, true);
        yield ";
        const url = `/room/\${roomID}/players`;

        async function refreshPlayers() {
            const response = await fetch(url);
            const data = await response.json();

            const list = document.getElementById('players-list');
            list.innerHTML = '';

            if (data.players.length === 0) {
                list.innerHTML = '<li>Aucun joueur</li>';
                document.getElementById('host-actions').style.display = 'none';
                return;
            }

            data.players.forEach((p, index) => {
                const li = document.createElement('li');
                li.textContent = p.username;

                // marquer le host
                if (index === 0) {
                    li.textContent += \" (host)\";
                }

                list.appendChild(li);
            });

            if (data.players[0].id === currentUserId) {
                document.getElementById('host-actions').style.display = 'block';
            } else {
                document.getElementById('host-actions').style.display = 'none';
            }

        }

        // refresh 2 seconds
        setInterval(refreshPlayers, 2000);

        // refresh when charged
        refreshPlayers();
    </script>


    <p><a href=\"";
        // line 72
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("homepage");
        yield "\">Retour</a></p>
    
</body>
</html>
";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "waitroom.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  134 => 72,  87 => 28,  83 => 27,  74 => 21,  64 => 13,  58 => 11,  56 => 10,  45 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html>
<head>
    <meta charset=\"UTF-8\">
    <title>Waitroom</title>
    <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
</head>
<body>
    <h1>Waiting...</h1>
    {% if start_button is defined %}
        <button href=\"{{ path('start_game') }}\">Lancer la partie</button>
    {% endif %}

    <h2>Joueurs présents :</h2>
    <ul id=\"players-list\">
        <li>Chargement…</li>
    </ul>

    <div id=\"host-actions\" style=\"display:none;\">
        <em>Vous êtes l'host de la partie</em>
        <form method=\"post\" action=\"{{ path('start_game', {'roomId': roomID}) }}\">
            <button type=\"submit\">Start Game</button>
        </form>
    </div>

    <script>
        const roomId = {{ roomID }};
        const userId = {{ userID }};
        const url = `/room/\${roomID}/players`;

        async function refreshPlayers() {
            const response = await fetch(url);
            const data = await response.json();

            const list = document.getElementById('players-list');
            list.innerHTML = '';

            if (data.players.length === 0) {
                list.innerHTML = '<li>Aucun joueur</li>';
                document.getElementById('host-actions').style.display = 'none';
                return;
            }

            data.players.forEach((p, index) => {
                const li = document.createElement('li');
                li.textContent = p.username;

                // marquer le host
                if (index === 0) {
                    li.textContent += \" (host)\";
                }

                list.appendChild(li);
            });

            if (data.players[0].id === currentUserId) {
                document.getElementById('host-actions').style.display = 'block';
            } else {
                document.getElementById('host-actions').style.display = 'none';
            }

        }

        // refresh 2 seconds
        setInterval(refreshPlayers, 2000);

        // refresh when charged
        refreshPlayers();
    </script>


    <p><a href=\"{{ path('homepage') }}\">Retour</a></p>
    
</body>
</html>
", "waitroom.twig", "/var/www/templates/waitroom.twig");
    }
}
