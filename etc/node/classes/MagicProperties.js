/**
 * A class to provide similar "magic" property functionality as PHP objects
 * have by utilising a Proxy object.
 *
 * Provides the following functionality:
 *  - __get       Called via `instance.property`
 *  - __set       Called via `instance.property = ...`
 *  - __isset     Called via `'property' in instance`
 *  - __unset     Called via `delete instance.property`
 *
 *  It additionally provides these two magic properties not available in PHP.
 *  - __getStatic Called via `class.property`
 *  - __setStatic Called via `class.property = ...`
 *
 * To use:
 *
 * import MagicProperties from "MagicProperties";
 *
 * const MyClass = MagicProperties(class MyClass { ... })
 *
 * module.exports = MyClass;
 *
 * @author Oliver Lillie
 * @param classDeclaration
 * @return {*}
 */
module.exports = function (classDeclaration) {
    // A toggle switch for the __isset method needed to control
    // `prop in instance` inside of getters
    let issetEnabled = true;

    const classHandler = Object.create(null);

    classHandler.construct = (target, args) => {
        const instance = new classDeclaration(...args);
        const instanceHandler = Object.create(null);

        /**
         * `__get` is invoked by calling
         *
         * let value = myClassInstance.magicProperty;
         *
         * @see http://php.net/manual/en/language.oop5.overloading.php#object.get
         */
        const get = Object.getOwnPropertyDescriptor(classDeclaration.prototype, '__get');
        if(get) {
            instanceHandler.get = (target, name, receiver) => {
                // We need to turn off the __isset() trap for the moment to
                // establish compatibility with PHP behaviour PHP's __get()
                // method doesn't care about its own __isset() method, so
                // neither should we
                issetEnabled = false;
                const exists = name in target;
                issetEnabled = true;

                if(exists) {
                    return target[name];
                }

                return receiver.__get(name);
            };
        }

        /**
         * `__set` is invoked by calling
         *
         * myClassInstance.magicProperty = "new value";
         *
         * @see http://php.net/manual/en/language.oop5.overloading.php#object.set
         */
        const set = Object.getOwnPropertyDescriptor(classDeclaration.prototype, '__set');
        if(set) {
            instanceHandler.set = (target, name, value) => {
                if(name in target) {
                    target[name] = value;
                }
                return target.__set.call(target, name, value);
            };
        }

        /**
         * `__isset` is invoked by calling
         *
         * if('magicProperty' in myClassInstance === true) {
         *     // this is set
         * }
         *
         * @see http://php.net/manual/en/language.oop5.overloading.php#object.isset
         */
        const isset = Object.getOwnPropertyDescriptor(classDeclaration.prototype, '__isset');
        if(isset) {
            instanceHandler.has = (target, name) => {
                if(!issetEnabled) {
                    return name in target;
                }
                return isset.value.call(target, name);
            };
        }

        /**
         * `__unset` is invoked by calling
         *
         * delete myClassInstance.magicProperty;
         *
         * @see http://php.net/manual/en/language.oop5.overloading.php#object.unset
         */
        const unset = Object.getOwnPropertyDescriptor(classDeclaration.prototype, '__unset');
        if(unset) {
            instanceHandler.deleteProperty = (target, name) => unset.value.call(target, name);
        }

        return new Proxy(instance, instanceHandler);
    };

    /**
     * `__getStatic` is invoked by calling
     *
     * let value = MyClass.magicProperty;
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php#object.callstatic
     */
    if(Object.getOwnPropertyDescriptor(classDeclaration, '__getStatic')) {
        classHandler.get = (target, name) => {
            if(name in target) {
                return target[name];
            }
            return target.__getStatic(name);
        };
    }

    /**
     * `__setStatic` is invoked by calling
     *
     * MyClass.magicProperty = "new value";
     */
    if(Object.getOwnPropertyDescriptor(classDeclaration, '__setStatic')) {
        classHandler.set = (target, name, value) => {
            if(name in target) {
                return target[name];
            }
            return target.__setStatic(name, value);
        };
    }

    return new Proxy(classDeclaration, classHandler);
};
