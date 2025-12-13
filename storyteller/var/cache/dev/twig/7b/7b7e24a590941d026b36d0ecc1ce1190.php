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

/* index.html.twig */
class __TwigTemplate_0519d1626e0f113629893b6031708715 extends Template
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
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "index.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"UTF-8\">
    <title>Accueil</title>
</head>
<body>
    ";
        // line 8
        if (array_key_exists("user_id", $context)) {
            // line 9
            yield "        <p>A ENLEVER APRES DEBUG</p>
        <p>Your ID: <strong>";
            // line 10
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 10, $this->source); })()), "html", null, true);
            yield "</strong></p>
    ";
        }
        // line 12
        yield "    <hr />

    ";
        // line 14
        if ((array_key_exists("username", $context) && (isset($context["username"]) || array_key_exists("username", $context) ? $context["username"] : (function () { throw new RuntimeError('Variable "username" does not exist.', 14, $this->source); })()))) {
            // line 15
            yield "        <p>Current username: <strong>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["username"]) || array_key_exists("username", $context) ? $context["username"] : (function () { throw new RuntimeError('Variable "username" does not exist.', 15, $this->source); })()), "html", null, true);
            yield "</strong></p>
    ";
        }
        // line 17
        yield "
    ";
        // line 18
        if ((array_key_exists("success", $context) && (isset($context["success"]) || array_key_exists("success", $context) ? $context["success"] : (function () { throw new RuntimeError('Variable "success" does not exist.', 18, $this->source); })()))) {
            // line 19
            yield "        <p style=\"color:green\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["success"]) || array_key_exists("success", $context) ? $context["success"] : (function () { throw new RuntimeError('Variable "success" does not exist.', 19, $this->source); })()), "html", null, true);
            yield "</p>
    ";
        }
        // line 21
        yield "    ";
        if ((array_key_exists("error", $context) && (isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 21, $this->source); })()))) {
            // line 22
            yield "        <p style=\"color:red\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 22, $this->source); })()), "html", null, true);
            yield "</p>
    ";
        }
        // line 24
        yield "
    <form method=\"post\" action=\"";
        // line 25
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("homepage");
        yield "\">
        <label for=\"username\">Username (letters, numbers, - and _ only):</label>
        <input id=\"username\" name=\"username\" type=\"text\" value=\"";
        // line 27
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("username", $context)) ? (Twig\Extension\CoreExtension::default((isset($context["username"]) || array_key_exists("username", $context) ? $context["username"] : (function () { throw new RuntimeError('Variable "username" does not exist.', 27, $this->source); })()), "")) : ("")), "html", null, true);
        yield "\" />
        <button type=\"submit\">Link username</button>
    </form>

    <div style=\"margin-top:20px\">
        <a href=\"";
        // line 32
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("create_waitroom_normal");
        yield "\" role=\"button\">Create normal game</a>
        &nbsp;|&nbsp;
        <a href=\"";
        // line 34
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("create_waitroom_path");
        yield "\" role=\"button\">Create path game</a>
        &nbsp;|&nbsp;
        <a href=\"";
        // line 36
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("join_game");
        yield "\" role=\"button\">Join game</a>
    </div>
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
        return "index.html.twig";
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
        return array (  122 => 36,  117 => 34,  112 => 32,  104 => 27,  99 => 25,  96 => 24,  90 => 22,  87 => 21,  81 => 19,  79 => 18,  76 => 17,  70 => 15,  68 => 14,  64 => 12,  59 => 10,  56 => 9,  54 => 8,  45 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html>
<head>
    <meta charset=\"UTF-8\">
    <title>Accueil</title>
</head>
<body>
    {% if user_id is defined %}
        <p>A ENLEVER APRES DEBUG</p>
        <p>Your ID: <strong>{{ user_id }}</strong></p>
    {% endif %}
    <hr />

    {% if username is defined and username %}
        <p>Current username: <strong>{{ username }}</strong></p>
    {% endif %}

    {% if success is defined and success %}
        <p style=\"color:green\">{{ success }}</p>
    {% endif %}
    {% if error is defined and error %}
        <p style=\"color:red\">{{ error }}</p>
    {% endif %}

    <form method=\"post\" action=\"{{ path('homepage') }}\">
        <label for=\"username\">Username (letters, numbers, - and _ only):</label>
        <input id=\"username\" name=\"username\" type=\"text\" value=\"{{ username|default('') }}\" />
        <button type=\"submit\">Link username</button>
    </form>

    <div style=\"margin-top:20px\">
        <a href=\"{{ path('create_waitroom_normal') }}\" role=\"button\">Create normal game</a>
        &nbsp;|&nbsp;
        <a href=\"{{ path('create_waitroom_path') }}\" role=\"button\">Create path game</a>
        &nbsp;|&nbsp;
        <a href=\"{{ path('join_game') }}\" role=\"button\">Join game</a>
    </div>
</body>
</html>
", "index.html.twig", "/var/www/templates/index.html.twig");
    }
}
