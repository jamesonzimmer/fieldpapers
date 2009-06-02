import sys
import time
import urllib
import httplib
import os.path
import datetime
import urlparse
import optparse
import decode

parser = optparse.OptionParser(usage="""poll.py [options]
""")

parser.add_option('-a', '--access', dest='access',
                  help='AWS access key',
                  action='store')

parser.add_option('-s', '--secret', dest='secret',
                  help='AWS secret key',
                  action='store')

parser.add_option('-p', '--password', dest='password',
                  help='Paperwalking queue password',
                  action='store')

parser.add_option('-b', '--apibase', dest='apibase',
                  help='URL root of queue API',
                  action='store')

def getMarkers():
    """
    """
    markers = {}

    for basename in ('Header', 'Hand', 'CCBYSA'):
        basepath = os.path.dirname(os.path.realpath(__file__)) + '/corners/' + basename
        markers[basename] = decode.Marker(basepath)

    return markers

if __name__ == '__main__':

    (options, args) = parser.parse_args()
    
    s, host, path, p, q, f = urlparse.urlparse(options.apibase)
    
    poll_failures = 0

    while True:
        try:
            params = urllib.urlencode({'timeout': 5, 'password': options.password})
            
            req = httplib.HTTPConnection(host, 80)
            req.request('POST', path+'/dequeue.php', params, {'Content-Type': 'application/x-www-form-urlencoded'})
            res = req.getresponse()
            
            assert res.status == 200
            
            # success means we drop back to zero
            poll_failures = 0
            
            try:
                message_id, url = res.read().split()
                message_id = int(message_id)
            except ValueError:
                # probably no queue message
                pass
            else:
                print >> sys.stderr, datetime.datetime.now(), 'Decoding message id', message_id, '-', url
                decode.main(url, getMarkers(), options.apibase, message_id, options.access, options.secret, options.password)

        except KeyboardInterrupt:
            raise

        except Exception, e:
            print >> sys.stderr, 'Something went wrong:', e

            poll_failures += 1
            
            if poll_failures > 5:
                print >> sys.stderr, 'No, seriously.'
                raise

        time.sleep(5)
