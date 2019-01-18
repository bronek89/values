<?php

namespace GW\Value;

final class PlainString implements StringValue
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function substring(int $start, ?int $length = null): PlainString
    {
        return new self(mb_substr($this->value, $start, $length));
    }

    /**
     * @param string|StringValue $other
     */
    public function postfix($other): PlainString
    {
        return new self($this->value . $this->castToString($other));
    }

    /**
     * @param string|StringValue $other
     */
    public function prefix($other): PlainString
    {
        return new self($this->castToString($other) . $this->value);
    }

    public function transform(callable $transformer): PlainString
    {
        return new self($transformer($this->value));
    }

    public function stripTags(): PlainString
    {
        return new self(strip_tags($this->value));
    }

    /**
     * @param string|StringValue $characterMask
     */
    public function trim($characterMask = self::TRIM_MASK): PlainString
    {
        return new self(trim($this->value, $this->castToString($characterMask)));
    }

    /**
     * @param string|StringValue $characterMask
     */
    public function trimRight($characterMask = self::TRIM_MASK): PlainString
    {
        return new self(rtrim($this->value, $characterMask));
    }

    /**
     * @param string|StringValue $characterMask
     */
    public function trimLeft($characterMask = self::TRIM_MASK): PlainString
    {
        return new self(ltrim($this->value, $this->castToString($characterMask)));
    }

    public function lower(): PlainString
    {
        return new self(mb_strtolower($this->value));
    }

    public function upper(): PlainString
    {
        return new self(mb_strtoupper($this->value));
    }

    public function lowerFirst(): PlainString
    {
        return $this->substring(0, 1)->lower()->postfix($this->substring(1));
    }

    public function upperFirst(): PlainString
    {
        return $this->substring(0, 1)->upper()->postfix($this->substring(1));
    }

    public function upperWords(): StringValue
    {
        return $this
            ->explode(' ')
            ->map(function (StringValue $word): StringValue {
                return $word->upperFirst();
            })
            ->implode(' ');
    }

    /**
     * @param string|StringValue $string
     */
    public function padRight(int $length, $string = ' '): PlainString
    {
        return new self(str_pad($this->value, $length, $this->castToString($string), STR_PAD_RIGHT));
    }

    /**
     * @param string|StringValue $string
     */
    public function padLeft(int $length, $string = ' '): PlainString
    {
        return new self(str_pad($this->value, $length, $this->castToString($string), STR_PAD_LEFT));
    }

    /**
     * @param string|StringValue $string
     */
    public function padBoth(int $length, $string = ' '): PlainString
    {
        return new self(str_pad($this->value, $length, $this->castToString($string), STR_PAD_BOTH));
    }

    /**
     * @param string|StringValue $search
     * @param string|StringValue $replace
     */
    public function replace($search, $replace): PlainString
    {
        return new self(str_replace($this->castToString($search), $this->castToString($replace), $this->value));
    }

    /**
     * @param string|StringValue $pattern
     * @param string|StringValue $replacement
     */
    public function replacePattern($pattern, $replacement): PlainString
    {
        return new self(preg_replace($this->castToString($pattern), $this->castToString($replacement), $this->value));
    }

    /**
     * @param string|StringValue $pattern
     */
    public function replacePatternCallback($pattern, callable $callback): PlainString
    {
        return new self(preg_replace_callback($this->castToString($pattern), $callback, $this->value));
    }

    /**
     * @param string|StringValue $pattern
     */
    public function matchPatterns($pattern): StringsArray
    {
        preg_match($this->castToString($pattern), $this->value, $matches);

        return Wrap::stringsArray($matches);
    }

    /**
     * @param string|StringValue $pattern
     */
    public function isMatching($pattern): bool
    {
        return $this->matchAllPatterns($this->castToString($pattern))->count() > 0;
    }

    /**
     * @param string|StringValue $pattern
     */
    public function startsWith($pattern): bool
    {
        return \mb_strpos($this->value, $this->castToString($pattern)) === 0;
    }

    /**
     * @param string|StringValue $pattern
     */
    public function endsWith($pattern): bool
    {
        $string = $this->castToString($pattern);
        $length = \mb_strlen($string);

        return $this->substring(-$length)->toString() === $string;
    }

    /**
     * @param string|StringValue $pattern
     */
    public function matchAllPatterns($pattern): ArrayValue
    {
        preg_match_all($this->castToString($pattern), $this->value, $matches, PREG_SET_ORDER);

        return Wrap::array($matches);
    }

    /**
     * @param string|StringValue $pattern
     */
    public function splitByPattern($pattern): StringsArray
    {
        return Wrap::stringsArray(preg_split($this->castToString($pattern), $this->value));
    }

    /**
     * @param string|StringValue $delimiter
     */
    public function explode($delimiter): StringsArray
    {
        return Wrap::stringsArray(explode($this->castToString($delimiter), $this->value));
    }

    /**
     * @param string|StringValue $postfix
     */
    public function truncate(int $length, $postfix = '...'): PlainString
    {
        if ($this->length() <= $length) {
            return $this;
        }

        return new self(mb_substr($this->value, 0, $length) . $this->castToString($postfix));
    }

    public function length(): int
    {
        return mb_strlen($this->value);
    }

    /**
     * @param string|StringValue $substring
     */
    public function contains($substring): bool
    {
        return $this->position($this->castToString($substring)) !== null;
    }

    /**
     * @param string|StringValue $needle
     */
    public function position($needle): ?int
    {
        return ($pos = mb_strpos($this->value, $this->castToString($needle))) !== false ? $pos : null;
    }

    /**
     * @param string|StringValue $needle
     */
    public function positionLast($needle): ?int
    {
        return ($pos = mb_strrpos($this->value, $this->castToString($needle))) !== false ? $pos : null;
    }

    public function toString(): string
    {
        return $this->value();
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    /**
     * @param mixed $value
     */
    private function castToString($value): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if ($value instanceof StringValue) {
            return $value->toString();
        }

        if (\is_scalar($value)) {
            return (string)$value;
        }

        if (\is_object($value) && \method_exists($value, '__toString')) {
            return $value->__toString();
        }

        throw new \TypeError('Value cannot be casted to string.');
    }
}
