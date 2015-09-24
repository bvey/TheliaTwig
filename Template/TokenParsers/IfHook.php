<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace TheliaTwig\Template\TokenParsers;

use TheliaTwig\Template\Node\IfHook as NodeIfHook;

/**
 * Class IfHook
 * @package TheliaTwig\Template\TokenParsers
 * @author Julien Chanséaume <julien@thelia.net>
 */
class IfHook extends \Twig_TokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A \Twig_Token instance
     *
     * @return \Twig_NodeInterface A \Twig_NodeInterface instance
     *
     * @throws \Twig_Error_Syntax
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $parser = $this->parser;
        $stream = $parser->getStream();

        $parameters = $parser->getExpressionParser()->parseHashExpression();

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $parser->subparse(array($this, 'decideEndHook'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new NodeIfHook($body, $parameters, $lineno, $this->getTag());
    }

    public function decideEndHook(\Twig_Token $token)
    {
        return $token->test(['endifhook']);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return "ifhook";
    }
}
