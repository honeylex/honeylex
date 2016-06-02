<?php

/* index.html.twig */
class __TwigTemplate_a4a4d6fadca1755541a583f96e44de34e591a0e9e7b707dac4c321cf894436e3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("layout.html.twig", "index.html.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_449665387b51b60a1fd32a8bb4456d718b2231b3eae0bd5657f91e797255cd57 = $this->env->getExtension("native_profiler");
        $__internal_449665387b51b60a1fd32a8bb4456d718b2231b3eae0bd5657f91e797255cd57->enter($__internal_449665387b51b60a1fd32a8bb4456d718b2231b3eae0bd5657f91e797255cd57_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "index.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_449665387b51b60a1fd32a8bb4456d718b2231b3eae0bd5657f91e797255cd57->leave($__internal_449665387b51b60a1fd32a8bb4456d718b2231b3eae0bd5657f91e797255cd57_prof);

    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        $__internal_4fbf528ff9d2d24569bcc04a686cd32011c52a8525bc5ae792d1311836f9e13c = $this->env->getExtension("native_profiler");
        $__internal_4fbf528ff9d2d24569bcc04a686cd32011c52a8525bc5ae792d1311836f9e13c->enter($__internal_4fbf528ff9d2d24569bcc04a686cd32011c52a8525bc5ae792d1311836f9e13c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "content"));

        // line 4
        echo "    Welcome to your new Silex Application!
";
        
        $__internal_4fbf528ff9d2d24569bcc04a686cd32011c52a8525bc5ae792d1311836f9e13c->leave($__internal_4fbf528ff9d2d24569bcc04a686cd32011c52a8525bc5ae792d1311836f9e13c_prof);

    }

    public function getTemplateName()
    {
        return "index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  40 => 4,  34 => 3,  11 => 1,);
    }
}
/* {% extends "layout.html.twig" %}*/
/* */
/* {% block content %}*/
/*     Welcome to your new Silex Application!*/
/* {% endblock %}*/
/* */
