<?php

/* @admin/\views\users.twig */
class __TwigTemplate_5115adb54b851a2c53f20a612c0c5c714456e6b27177118381c5b0031c0c5f16 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<div class=\"container-fluid users-list\">
    <table class=\"table table-hover\">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Dâte d'enregistrement</th>
                <th>E-mail</th>
            </tr>
        </thead>
        <tbody>
           ";
        // line 11
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["ctrl"]) ? $context["ctrl"] : null), "userList", array()));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["user"]) {
            // line 12
            echo "            <tr>
                <th>
                    <img width=\"45\" height=\"45\" src=\"";
            // line 14
            echo (isset($context["base_url"]) ? $context["base_url"] : null);
            echo $this->getAttribute($context["user"], "avatar", array());
            echo "\" alt=\"My profile\" class=\"img-circle avatar\">
                    <a href=\"user-profile.html\" class=\"name\">";
            // line 15
            echo $this->getAttribute($context["user"], "username", array());
            echo "</a>
                    <span class=\"subtext\">Développeur</span>
                </th>
                <th>";
            // line 18
            echo twig_date_format_filter($this->env, $this->getAttribute($context["user"], "signup", array()));
            echo "</th>
                <th>";
            // line 19
            echo $this->getAttribute($context["user"], "email", array());
            echo "</th>
            </tr>
            ";
            $context['_iterated'] = true;
        }
        if (!$context['_iterated']) {
            // line 22
            echo "            <span>Un problèmes est survenue.</span>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['user'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 24
        echo "        </tbody>
    </table>
</div>
";
    }

    public function getTemplateName()
    {
        return "@admin/\\views\\users.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  70 => 24,  63 => 22,  55 => 19,  51 => 18,  45 => 15,  40 => 14,  36 => 12,  31 => 11,  19 => 1,);
    }
}
/* <div class="container-fluid users-list">*/
/*     <table class="table table-hover">*/
/*         <thead>*/
/*             <tr>*/
/*                 <th>Nom</th>*/
/*                 <th>Dâte d'enregistrement</th>*/
/*                 <th>E-mail</th>*/
/*             </tr>*/
/*         </thead>*/
/*         <tbody>*/
/*            {% for user in ctrl.userList %}*/
/*             <tr>*/
/*                 <th>*/
/*                     <img width="45" height="45" src="{{base_url}}{{user.avatar}}" alt="My profile" class="img-circle avatar">*/
/*                     <a href="user-profile.html" class="name">{{ user.username }}</a>*/
/*                     <span class="subtext">Développeur</span>*/
/*                 </th>*/
/*                 <th>{{ user.signup | date }}</th>*/
/*                 <th>{{ user.email }}</th>*/
/*             </tr>*/
/*             {% else %}*/
/*             <span>Un problèmes est survenue.</span>*/
/*             {% endfor %}*/
/*         </tbody>*/
/*     </table>*/
/* </div>*/
/* */
