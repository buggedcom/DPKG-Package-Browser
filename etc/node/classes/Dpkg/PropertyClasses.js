import Architecture from './Property/Architecture';
import Breaks from './Property/Breaks';
import Bugs from './Property/Bugs';
import BuiltUsing from './Property/BuiltUsing';
import Conffiles from './Property/Conffiles';
import Conflicts from './Property/Conflicts';
import Depends from './Property/Depends';
import Description from './Property/Description';
import Enhances from './Property/Enhances';
import Essential from './Property/Essential';
import Homepage from './Property/Homepage';
import InstalledSize from './Property/InstalledSize';
import Maintainer from './Property/Maintainer';
import MultiArch from './Property/MultiArch';
import Origin from './Property/Origin';
import OriginalMaintainer from './Property/OriginalMaintainer';
import Package from './Property/Package';
import PreDepends from './Property/PreDepends';
import Priority from './Property/Priority';
import Provides from './Property/Provides';
import Recommends from './Property/Recommends';
import Replaces from './Property/Replaces';
import Section from './Property/Section';
import Source from './Property/Source';
import Status from './Property/Status';
import Suggests from './Property/Suggests';
import Version from './Property/Version';

/**
 * A simple object hash for easy accessing of Property object constructors.
 *
 * @author Oliver Lillie
 * @type {{Bugs: Bugs, Origin: Origin, Description: Description, Enhances: Enhances, Recommends: Recommends, Essential: Essential, BuiltUsing: BuiltUsing, Source: Source, Maintainer: Maintainer, OriginalMaintainer: OriginalMaintainer, Version: Version, Suggests: Suggests, PreDepends: PreDepends, Status: Status, Breaks: Breaks, Homepage: Homepage, Architecture: Architecture, Conffiles: Conffiles, Priority: Priority, Conflicts: Conflicts, Replaces: Replaces, Provides: Provides, Section: Section, MultiArch: MultiArch, Package: Package, Depends: Depends, InstalledSize: InstalledSize}}
 */
module.exports = {
    Architecture,
    Breaks,
    Bugs,
    BuiltUsing,
    Conffiles,
    Conflicts,
    Depends,
    Description,
    Enhances,
    Essential,
    Homepage,
    InstalledSize,
    Maintainer,
    MultiArch,
    Origin,
    OriginalMaintainer,
    Package,
    PreDepends,
    Priority,
    Provides,
    Recommends,
    Replaces,
    Section,
    Source,
    Status,
    Suggests,
    Version
};