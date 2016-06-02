<?php

/* @WebProfiler/Collector/router.html.twig */
class __TwigTemplate_2e6e8808748be6bf6b0dc5e6bfb5b15ed3752c85ec016df35026036828517e10 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "@WebProfiler/Collector/router.html.twig", 1);
        $this->blocks = array(
            'toolbar' => array($this, 'block_toolbar'),
            'menu' => array($this, 'block_menu'),
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_d6c0fd0569d7dd8735d025b2ebb63976bfc6c856a6f6e22732b37f096ccef688 = $this->env->getExtension("native_profiler");
        $__internal_d6c0fd0569d7dd8735d025b2ebb63976bfc6c856a6f6e22732b37f096ccef688->enter($__internal_d6c0fd0569d7dd8735d025b2ebb63976bfc6c856a6f6e22732b37f096ccef688_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@WebProfiler/Collector/router.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_d6c0fd0569d7dd8735d025b2ebb63976bfc6c856a6f6e22732b37f096ccef688->leave($__internal_d6c0fd0569d7dd8735d025b2ebb63976bfc6c856a6f6e22732b37f096ccef688_prof);

    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
        $__internal_9d2560232e6005a939a8d84d855dade3e85add365ba21a76b7bad5771d8a3cb5 = $this->env->getExtension("native_profiler");
        $__internal_9d2560232e6005a939a8d84d855dade3e85add365ba21a76b7bad5771d8a3cb5->enter($__internal_9d2560232e6005a939a8d84d855dade3e85add365ba21a76b7bad5771d8a3cb5_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        
        $__internal_9d2560232e6005a939a8d84d855dade3e85add365ba21a76b7bad5771d8a3cb5->leave($__internal_9d2560232e6005a939a8d84d855dade3e85add365ba21a76b7bad5771d8a3cb5_prof);

    }

    // line 5
    public function block_menu($context, array $blocks = array())
    {
        $__internal_54092dbd3738a8dd493952b6d479331c4b527c8409b60cb85cf107193f194848 = $this->env->getExtension("native_profiler");
        $__internal_54092dbd3738a8dd493952b6d479331c4b527c8409b60cb85cf107193f194848->enter($__internal_54092dbd3738a8dd493952b6d479331c4b527c8409b60cb85cf107193f194848_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "menu"));

        // line 6
        echo "<span class=\"label\">
    <span class=\"icon\">";
        // line 7
        echo twig_include($this->env, $context, "@WebProfiler/Icon/router.svg");
        echo "</span>
    <strong>Routing</strong>
</span>
";
        
        $__internal_54092dbd3738a8dd493952b6d479331c4b527c8409b60cb85cf107193f194848->leave($__internal_54092dbd3738a8dd493952b6d479331c4b527c8409b60cb85cf107193f194848_prof);

    }

    // line 12
    public function block_panel($context, array $blocks = array())
    {
        $__internal_ba49f3e1f1eaad7eea5e11b3f23f7ed332c471e478d9889714c2c8bacf851ba4 = $this->env->getExtension("native_profiler");
        $__internal_ba49f3e1f1eaad7eea5e11b3f23f7ed332c471e478d9889714c2c8bacf851ba4->enter($__internal_ba49f3e1f1eaad7eea5e11b3f23f7ed332c471e478d9889714c2c8bacf851ba4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        // line 13
        echo "    ";
        echo $this->env->getExtension('http_kernel')->renderFragment($this->env->getExtension('routing')->getPath("_profiler_router", array("token" => (isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")))));
        echo "
";
        
        $__internal_ba49f3e1f1eaad7eea5e11b3f23f7ed332c471e478d9889714c2c8bacf851ba4->leave($__internal_ba49f3e1f1eaad7eea5e11b3f23f7ed332c471e478d9889714c2c8bacf851ba4_prof);

    }

    public function getTemplateName()
    {
        return "@WebProfiler/Collector/router.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  73 => 13,  67 => 12,  56 => 7,  53 => 6,  47 => 5,  36 => 3,  11 => 1,);
    }
}
/* {% extends '@WebProfiler/Profiler/layout.html.twig' %}*/
/* */
/* {% block toolbar %}{% endblock %}*/
/* */
/* {% block menu %}*/
/* <span class="label">*/
/*     <span class="icon">{{ include('@WebProfiler/Icon/router.svg') }}</span>*/
/*     <strong>Routing</strong>*/
/* </span>*/
/* {% endblock %}*/
/* */
/* {% block panel %}*/
/*     {{ render(path('_profiler_router', { token: token })) }}*/
/* {% endblock %}*/
/* */
