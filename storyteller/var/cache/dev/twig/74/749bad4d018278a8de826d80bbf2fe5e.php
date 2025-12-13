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

/* structure/index.html.twig */
class __TwigTemplate_e5e5a7267facaaffebb7cd2210c2cd48 extends Template
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
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "structure/index.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Taleroom -prac</title>
 
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css\" integrity=\"sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N\" crossorigin=\"anonymous\">
    <link rel=\"stylesheet\" href=\"styles.css\">

    <link rel=\"icon\" href=\"favicon.ico\" type=\"image/x-icon\">
    <link rel=\"icon\" href=\"logo.png\" type=\"image/png\"> 
    <link rel=\"apple-touch-icon\" href=\"apple-touch-icon.png\">

</head>
<body>
    <div class=\"main-wrapper\">

        <!--barre de navegation-->
        <header class=\"main-header\">
            <nav class=\"navbar navbar-expand-sm navbar-light p-4\">
                <a href=\"#\"><img src=\"TaleRoom(1).png\" alt=\"TaleRoom\"></a>

                <ul class=\"navbar-nav ml-auto\">
                    <li class=\"nav-items user-profile\">

                            <a class=\"nav-link\" href=\"/profile\">
                            <span class=\"user-icon\"></span> 
                            Nickname
                        </a>
                        
                    </li>
                </ul>    
            </nav>
        </header>

        <main class=\"container-fluid hero-content-center d-flex justify-content-center align-items-center flex-column\">
            <div class=\"logotype-central mb-5\">
                <img src=\"TaleRoom.png\" alt=\"TaleRoom\">
            </div>
            
            <div class=\"row w-100 justify-content-center main-cta-row\">
                 <!--creer une nouvelle histoire-->
                <div class=\"col-12 col-md-4 mb-3 mb-md-0 d-flex justify-content-center\">
                    <a href=\"/new-story\" class=\"btn cta-box-button primary-cta-box\">
                       <span class=\"cta-large-text\"> Creer</span>
                        une nouvelle histoire
                    </a>
                </div>

                <!--Rejoindre a une nouvelle histoire--> 
                <div class=\"col-12 col-md-4 d-flex justify-content-center\">
                    <a href=\"/join-code\" class=\"btn cta-box-button primary-cta-box\">
                       <span class=\"cta-large-text\">  Rejoindre</span>
                        une histoire avec code
                    </a>
                </div>
            </div>

        </main>

        <footer class=\"main-footer\">
        </footer>
        
    </div>

    <script src=\"https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js\" integrity=\"sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj\" crossorigin=\"anonymous\"></script>
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js\" integrity=\"sha384-Fy6S30/35+p7+LqY1B+hL1FfM7fR+I7o2U6q+y2L\" crossorigin=\"anonymous\"></script>
</body>
</html>";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "structure/index.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  45 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Taleroom -prac</title>
 
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css\" integrity=\"sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N\" crossorigin=\"anonymous\">
    <link rel=\"stylesheet\" href=\"styles.css\">

    <link rel=\"icon\" href=\"favicon.ico\" type=\"image/x-icon\">
    <link rel=\"icon\" href=\"logo.png\" type=\"image/png\"> 
    <link rel=\"apple-touch-icon\" href=\"apple-touch-icon.png\">

</head>
<body>
    <div class=\"main-wrapper\">

        <!--barre de navegation-->
        <header class=\"main-header\">
            <nav class=\"navbar navbar-expand-sm navbar-light p-4\">
                <a href=\"#\"><img src=\"TaleRoom(1).png\" alt=\"TaleRoom\"></a>

                <ul class=\"navbar-nav ml-auto\">
                    <li class=\"nav-items user-profile\">

                            <a class=\"nav-link\" href=\"/profile\">
                            <span class=\"user-icon\"></span> 
                            Nickname
                        </a>
                        
                    </li>
                </ul>    
            </nav>
        </header>

        <main class=\"container-fluid hero-content-center d-flex justify-content-center align-items-center flex-column\">
            <div class=\"logotype-central mb-5\">
                <img src=\"TaleRoom.png\" alt=\"TaleRoom\">
            </div>
            
            <div class=\"row w-100 justify-content-center main-cta-row\">
                 <!--creer une nouvelle histoire-->
                <div class=\"col-12 col-md-4 mb-3 mb-md-0 d-flex justify-content-center\">
                    <a href=\"/new-story\" class=\"btn cta-box-button primary-cta-box\">
                       <span class=\"cta-large-text\"> Creer</span>
                        une nouvelle histoire
                    </a>
                </div>

                <!--Rejoindre a une nouvelle histoire--> 
                <div class=\"col-12 col-md-4 d-flex justify-content-center\">
                    <a href=\"/join-code\" class=\"btn cta-box-button primary-cta-box\">
                       <span class=\"cta-large-text\">  Rejoindre</span>
                        une histoire avec code
                    </a>
                </div>
            </div>

        </main>

        <footer class=\"main-footer\">
        </footer>
        
    </div>

    <script src=\"https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js\" integrity=\"sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj\" crossorigin=\"anonymous\"></script>
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js\" integrity=\"sha384-Fy6S30/35+p7+LqY1B+hL1FfM7fR+I7o2U6q+y2L\" crossorigin=\"anonymous\"></script>
</body>
</html>", "structure/index.html.twig", "/var/www/templates/structure/index.html.twig");
    }
}
