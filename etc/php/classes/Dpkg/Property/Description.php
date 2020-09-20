<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\Dpkg\Property;

/**
 * Class Description
 * Holds the package "Description" property.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 * @property string $summary
 */
class Description extends Property {

    /**
     * The private property for the public accessor of "summary".
     *
     * @type null|string|array
     * @author Oliver Lillie
     */
    protected $_summary;

    /**
     * Description constructor.
     *
     * If the value is a string, then we split it out so the first line of the
     * description is treated as the summary line of the description.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $property_string
     * @param string $property
     * @param string|null $value
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $property_string, string $property, ?string $value) {
        // the first line of the description is always a summary line
        $summary = null;
        if(is_string($value) === true) {
            [$summary, $value] = explode("\n", $value, 2);
            $value = ltrim($value);
        }

        parent::__construct($property_string, $property, $value);

        $this->_setAccessibleProperties(
            [
                'summary',
            ]
        );

        $this->summary = $summary;
    }

    /**
     * The description string is in a "wrapped" state and has various special
     * notations that required processing so that the description value in the
     * frontend are correctly rendered.
     *
     * @author Oliver Lillie
     *
     * @param string|null $value
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property
     */
    public function setValue(?string $value): Property {
        if ($value !== null) {
            $previous_line = null;
            $lines = array_map(function ($line) use (&$previous_line) {
                // a single `.` denotes a paragraph break
                if (trim(substr($line, 0)) === '.') {
                    $previous_line = $line;

                    return PHP_EOL . PHP_EOL;
                }

                // a double space indicates a line break
                if (strpos($line, '  ') === 0) {
                    $previous_line = $line;

                    return PHP_EOL . substr($line, 1);
                }

                // If the previous line was a paragraph break then the line
                // after contains extra whitespace so must be removed.
                if ($previous_line === ' .') {
                    $line = trim($line);
                }

                $previous_line = $line;

                return $line;
            }, explode("\n", $value));
        } else {
            $lines = [];
        }

        $this->_value = implode('', $lines);

        return $this;
    }

    /**
     * The setter for setting the "summary" value.
     *
     * @author Oliver Lillie
     *
     * @param $summary
     *
     * @return $this
     */
    public function setSummary(?string $summary): self {
        $this->_summary = $summary;

        return $this;
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @return mixed|string
     */
    public function encodeForJson() {
        return [
            'summary' => $this->summary,
            'verbose' => $this->value,
        ];
    }

}