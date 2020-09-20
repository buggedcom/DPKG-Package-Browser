import Property from '../Property';
import Contact from '../Contact';
import addrs from 'email-addresses';
import ow from "ow";
import arg from "../../Arg";

/**
 * Class _HasEmail
 *
 * Processes properties that contain email addresses.
 *
 * @author Oliver Lillie
 */
class _HasEmail extends Property {

    /**
     * Sets and parses the email address details out of the value.
     *
     * The email addresses are set into the class as an array of Contact
     * instances.
     *
     * @author Oliver Lillie
     * @param {string|null} value
     */
    set value(value) {
        arg(value, ow.any(
            ow.string.nonEmpty,
            ow.null
        ));

        let addresses = value === null ? [] : addrs.parseAddressList(value) || [];
        super.value = addresses.map((contact) => new Contact(contact.address, contact.name));
    }

    get value() {
        return super.value;
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {Object[]}
     */
    encodeForJson() {
        return this._value.map((item) => item.encodeForJson());
    }
}

module.exports = _HasEmail;

