<?php 
interface IResolverSimple{
    /**
     * Resolve the domain name C{name} into an IP address.
     * 
     * @type name: C{str}
     * @type timeout: C{tuple}
     * @rtype: L{twisted.internet.defer.Deferred}
     * @return: The callback of the Deferred that is returned will be
     * passed a string that represents the IP address of the specified
     * name, or the errback will be called if the lookup times out.  If
     * multiple types of address records are associated with the name,
     * A6 records will be returned in preference to AAAA records, which
     * will be returned in preference to A records.  If there are multiple
     * records of the type to be returned, one will be selected at random.
     * 
     * @raise twisted.internet.defer.TimeoutError: Raised (asynchronously)
     * if the name cannot be resolved within the specified timeout period.
     */

    public function getHostByName($name, array $timeout = array(1, 3, 11, 45));
}

