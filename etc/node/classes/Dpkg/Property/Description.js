import Property from '../Property';
import PropertyHelper from '../PropertyHelper';
import StrTrim from 'str-trim';
import ow from "ow";
import arg from "../../Arg";

/**
 * Class Description
 * Holds the package "Description" property.
 *
 * @author Oliver Lillie
 * @property {string} summary
 */
class Description extends Property {

    /**
     * Description constructor.
     *
     * If the value is a string, then we split it out so the first line of the
     * description is treated as the summary line of the description.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {string} propertyString
     * @param {string} property
     * @param {string|null} value
     */
    constructor(propertyString, property, value) {
        // propertyString, property and value are validated through the super
        // setters

        // the first line of the description is always a summary line
        let summary = null;
        if(typeof value === 'string') {
            const parts = PropertyHelper.splitLimited(value, "\n", 2);
            summary = parts[0];
            value = parts[1] && parts[1].ltrim() || '';
        }

        super(propertyString, property, value);
        
        this.summary = summary;
    }

    /**
     * The private property for the public accessor of "summary".
     *
     * @type {null|string}
     * @author Oliver Lillie
     */
    set summary(summary) {
        arg(summary, ow.string);

        this._summary = summary;
    }

    get summary() {
        return this._summary;
    }

    /**
     * The description string is in a "wrapped" state and has various special
     * notations that required processing so that the description value in the
     * frontend are correctly rendered.
     *
     * @author Oliver Lillie
     * @param {string|null} value
     */
    set value(value) {
        arg(value, ow.any(
            ow.string,
            ow.null
        ));

        let lines = [];
        if (value !== null) {
            let previousLine = null;
            lines = value.split("\n").map((line) => {

                // a single `.` denotes a paragraph break
                if (line.substr(0).trim() === '.') {
                    previousLine = line;

                    return "\n\n";
                }

                // a double space indicates a line break
                if (line.indexOf('  ') === 0) {
                    previousLine = line;

                    return "\n" . substr(line, 1);
                }

                // If the previous line was a paragraph break then the line
                // after contains extra whitespace so must be removed.
                if (previousLine === ' .') {
                    line = line.trim();
                }

                previousLine = line;

                return line;
            });
        }

        super.value = lines.join('');
    }

    get value () {
        return super.value;
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {{summary: string, verbose: (string|Array)}}
     */
    encodeForJson() {
        return {
            summary: this._summary,
            verbose: this._value
        };
    }
}

module.exports = Description;
