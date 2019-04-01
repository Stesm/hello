<?php

namespace Core\Commands;
/**
 * Class Cmd
 * @package Commands
 */
class Cmd
{
    /** @var array $commands */
    private $commands = [
        'migrate' => Migrator\Migrator::class,
        'make' => Generator::class,
    ];

    /** @var array $params */
    private $params = [];

    /** @var null|\ReflectionClass $command */
    private $command = null;

    /** @var null|\ReflectionMethod $action */
    private $action = null;

    /**
     * Cmd constructor.
     * @param array $argv
     * @throws \Exception
     */
    public function __construct(array $argv)
    {
        $argv = array_slice($argv, 1);
        $command = $argv[0] ?? null;
        $action = $argv[1] ?? null;

        if (!$command || !array_key_exists($command, $this->commands))
            $this->throwHelp('Wrong command!');

        $this->command = new \ReflectionClass($this->commands[$command]);

        if (!$this->command->hasMethod($action))
            $this->throwHelp('Wrong action!');

        $this->action = $this->command->getMethod($action);
        $this->params = array_slice($argv, 2);
    }

    /**
     * @param null $message
     * @throws \Exception
     */
    private function throwHelp($message = null)
    {
        $docs = [];
        foreach ($this->commands as $command => $class) {
            try {
                $ref = new \ReflectionClass($class);
                $doc = "    - \e[1;32m{$command}\e[0m : {$this->extractComment($ref->getDocComment())}\n";

                $actions = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($actions as $action) {
                    if (strpos($action->name, '__') === 0)
                        continue;

                    $doc .= "        - \e[1;33m{$action->name}\e[0m : {$this->extractComment($action->getDocComment())}\n";
                }

                $docs[] = $doc;
            } catch (\Throwable $e) {}
        }

        $text = implode(PHP_EOL, $docs);

        if ($message)
            $text = "\e[1;41m ERROR :: ".$message." \e[0m\n\n".$text;

        throw new \Exception($text);
    }

    private function extractComment($str)
    {
        $doc = array_filter(array_map(function ($item) {
            return !preg_match('/\*\s@|\*\*|\*\//i', $item) ? preg_replace('/\s+\*\s/', '', $item) : false;
        }, explode("\n",$str)));

        return implode(' ',$doc);
    }

    public function launch()
    {
        $this->action->invokeArgs($this->command->newInstance(), $this->params);
    }
}
