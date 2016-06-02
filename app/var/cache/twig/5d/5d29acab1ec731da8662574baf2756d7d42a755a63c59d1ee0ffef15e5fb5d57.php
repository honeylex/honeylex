<?php

/* layout.html.twig */
class __TwigTemplate_2c799001930c712ce1f377d2886fdc28a3bc53a1654806e546d64245f06bca2e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_2a5c0173740e803ba05a3e1a5806151a67f466c24663411b4bc7e837d5dfc8f1 = $this->env->getExtension("native_profiler");
        $__internal_2a5c0173740e803ba05a3e1a5806151a67f466c24663411b4bc7e837d5dfc8f1->enter($__internal_2a5c0173740e803ba05a3e1a5806151a67f466c24663411b4bc7e837d5dfc8f1_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "layout.html.twig"));

        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <title>";
        // line 4
        $this->displayBlock('title', $context, $blocks);
        echo " - My Silex Application</title>

        <link href=\"";
        // line 6
        echo twig_escape_filter($this->env, $this->env->getExtension('asset')->getAssetUrl("css/main.css"), "html", null, true);
        echo "\" rel=\"stylesheet\" type=\"text/css\" />

        <script type=\"text/javascript\">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-XXXXXXXX-X']);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>
    </head>
    <body>
        ";
        // line 21
        $this->displayBlock('content', $context, $blocks);
        // line 22
        echo "    </body>
</html>
";
        
        $__internal_2a5c0173740e803ba05a3e1a5806151a67f466c24663411b4bc7e837d5dfc8f1->leave($__internal_2a5c0173740e803ba05a3e1a5806151a67f466c24663411b4bc7e837d5dfc8f1_prof);

    }

    // line 4
    public function block_title($context, array $blocks = array())
    {
        $__internal_97fb6a6a4fc89634edc276931e82177ed78d011abbf0564c2bf97f5a9f645760 = $this->env->getExtension("native_profiler");
        $__internal_97fb6a6a4fc89634edc276931e82177ed78d011abbf0564c2bf97f5a9f645760->enter($__internal_97fb6a6a4fc89634edc276931e82177ed78d011abbf0564c2bf97f5a9f645760_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        echo "";
        
        $__internal_97fb6a6a4fc89634edc276931e82177ed78d011abbf0564c2bf97f5a9f645760->leave($__internal_97fb6a6a4fc89634edc276931e82177ed78d011abbf0564c2bf97f5a9f645760_prof);

    }

    // line 21
    public function block_content($context, array $blocks = array())
    {
        $__internal_1b55dd58e8fe6ee8a57ec76443c36b0912cbedd3ff2e7f0acf48ad1db35034de = $this->env->getExtension("native_profiler");
        $__internal_1b55dd58e8fe6ee8a57ec76443c36b0912cbedd3ff2e7f0acf48ad1db35034de->enter($__internal_1b55dd58e8fe6ee8a57ec76443c36b0912cbedd3ff2e7f0acf48ad1db35034de_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "content"));

        
        $__internal_1b55dd58e8fe6ee8a57ec76443c36b0912cbedd3ff2e7f0acf48ad1db35034de->leave($__internal_1b55dd58e8fe6ee8a57ec76443c36b0912cbedd3ff2e7f0acf48ad1db35034de_prof);

    }

    public function getTemplateName()
    {
        return "layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  75 => 21,  63 => 4,  54 => 22,  52 => 21,  34 => 6,  29 => 4,  24 => 1,);
    }
}
/* <!DOCTYPE html>*/
/* <html>*/
/*     <head>*/
/*         <title>{% block title '' %} - My Silex Application</title>*/
/* */
/*         <link href="{{ asset('css/main.css') }}" rel="stylesheet" type="text/css" />*/
/* */
/*         <script type="text/javascript">*/
/*             var _gaq = _gaq || [];*/
/*             _gaq.push(['_setAccount', 'UA-XXXXXXXX-X']);*/
/*             _gaq.push(['_trackPageview']);*/
/* */
/*             (function() {*/
/*                 var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;*/
/*                 ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';*/
/*                 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);*/
/*             })();*/
/*         </script>*/
/*     </head>*/
/*     <body>*/
/*         {% block content %}{% endblock %}*/
/*     </body>*/
/* </html>*/
/* */
