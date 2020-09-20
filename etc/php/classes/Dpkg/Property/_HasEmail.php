<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\Dpkg\Contact;
use DpkgBrowser\Classes\Dpkg\Property;

/**
 * Class _HasEmail
 *
 * Processes properties that contain email addresses.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 * @property \DpkgBrowser\Classes\Dpkg\Contact[] $value
 */
class _HasEmail extends Property {

    /**
     * Sets and parses the email address details out of the value.
     *
     * The email addresses are set into the class as an array of Contact
     * instances.
     *
     * @author Oliver Lillie
     *
     * @param string|null $value
     *
     * @return $this|\DpkgBrowser\Classes\Dpkg\Property
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function setValue(?string $value): Property {
        $contacts = $value === null ? [] : \mailparse_rfc822_parse_addresses($value);
        foreach ($contacts as $index => $contact) {
            // not that the address is rtrim'd with ; - since if the email
            // address is determined to be a newsgroup then
            // mailparse_rfc822_parse_addresses adds a ; to the end of the
            // address which invalidates the email address validation.
            $contacts[$index] = new Contact(rtrim($contact['address'], ';'), $contact['display']);
        }

        $this->_value = $contacts;

        return $this;
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     */
    public function encodeForJson() {
        return array_map(
            function ($item) {
                return $item->encodeForJson();
            },
            $this->_value
        );
    }

}