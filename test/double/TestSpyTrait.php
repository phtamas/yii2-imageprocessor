<?php


namespace phtamas\yii2\imageprocessor\test\double;

trait TestSpyTrait
{
    private $testSpyMethodCalls = [];

    /**
     * @param string $methodName
     * @return int
     */
    public function testSpyGetMethodCallCount($methodName)
    {
        return count($this->testSpyGetMethodCallsByMethodName($methodName));
    }

    /**
     * @param string $methodName
     * @return array[]
     */
    public function testSpyGetMethodCallArguments($methodName)
    {
        return array_map(
            function ($call) {
                return $call['arguments'];
            },
            $this->testSpyGetMethodCallsByMethodName($methodName)
        );
    }

    /**
     * @param $methodName
     * @param null|array $arguments
     * @return bool
     */
    public function testSpyHasRecordedMethodCall($methodName, array $arguments = null)
    {
        $calls = $this->testSpyGetMethodCallsByMethodName($methodName);
        if (!$calls) {
            return false;
        }
        if (is_null($arguments)) {
            return true;
        }
        return 0 != count(array_filter($calls, function ($call) use ($arguments) {
            return $call['arguments'] === $arguments;
        }));
    }

    /**
     * @param $position
     * @return null
     */
    public function testSpyGetMethodCallAtPosition($position)
    {
        return isset($this->testSpyMethodCalls[$position - 1]) ? $this->testSpyMethodCalls[$position - 1] : null;
    }

    public function testSpyReset()
    {
        $this->testSpyMethodCalls = [];
    }

    private function testSpyRecordMethodCall(array $arguments = null)
    {
        $callerData = debug_backtrace(false, 2)[1];
        $this->testSpyMethodCalls[] = [
            'methodName' => $callerData['function'],
            'arguments' => is_null($arguments) ? $callerData['args'] : $arguments,
        ];
    }

    /**
     * @param string $methodName
     * @return array[]
     */
    private function testSpyGetMethodCallsByMethodName($methodName)
    {
        return array_values(array_filter(
            $this->testSpyMethodCalls,
            function ($methodCall) use ($methodName) {
                return $methodCall['methodName'] === $methodName;
            }
        ));
    }
} 