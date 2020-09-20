/**
 * Class Contact
 *
 * The base property class that is used for package properties.
 *
 * @author Oliver Lillie
 * @property {string} display
 * @property {string} address
 */
class Contact {

    /**
     * Property constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {string} address
     * @param {string|null} display
     */
    constructor(address, display) {
        this.address = address;
        this.display = display;
    }

    /**
     * The private property for the public accessor of "address".
     *
     * @type {string}
     * @author Oliver Lillie
     */
    set address(address) {
        this._address = address;
    }
    get address() {
        return this._address;
    }

    /**
     * The private property for the public accessor of "display".
     *
     * @type {string|null}
     * @author Oliver Lillie
     */
    set display(display) {
        this._display = display;
    }
    get display() {
        return this._display;
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {{address: string, display: string}}
     */
    encodeForJson() {
        return {
            address: this._address,
            display: this._display
        };
    }

}

module.exports = Contact;