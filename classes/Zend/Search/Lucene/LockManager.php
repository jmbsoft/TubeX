<?php


require_once 'Zend/Search/Lucene/Storage/Directory.php';

require_once 'Zend/Search/Lucene/Storage/File.php';

class Zend_Search_Lucene_LockManager
{

    const WRITE_LOCK_FILE                = 'write.lock.file';
    const READ_LOCK_FILE                 = 'read.lock.file';
    const READ_LOCK_PROCESSING_LOCK_FILE = 'read-lock-processing.lock.file';
    const OPTIMIZATION_LOCK_FILE         = 'optimization.lock.file';

    public static function obtainWriteLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->createFile(self::WRITE_LOCK_FILE);
        if (!$lock->lock(LOCK_EX)) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Can\'t obtain exclusive index lock');
        }
        return $lock;
    }

    public static function releaseWriteLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->getFileObject(self::WRITE_LOCK_FILE);
        $lock->unlock();
    }

    private static function _startReadLockProcessing(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->createFile(self::READ_LOCK_PROCESSING_LOCK_FILE);
        if (!$lock->lock(LOCK_EX)) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Can\'t obtain exclusive lock for the read lock processing file');
        }
        return $lock;
    }

    private static function _stopReadLockProcessing(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->getFileObject(self::READ_LOCK_PROCESSING_LOCK_FILE);
        $lock->unlock();
    }

    public static function obtainReadLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->createFile(self::READ_LOCK_FILE);
        if (!$lock->lock(LOCK_SH)) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Can\'t obtain shared reading index lock');
        }
        return $lock;
    }

    public static function releaseReadLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->getFileObject(self::READ_LOCK_FILE);
        $lock->unlock();
    }

    public static function escalateReadLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        self::_startReadLockProcessing($lockDirectory);

        $lock = $lockDirectory->getFileObject(self::READ_LOCK_FILE);

        // First, release the shared lock for the benefit of GFS since
        // it will fail the conditional request to promote the lock to
        // "exclusive" while the shared lock is held (even when we are
        // the only holder).
        $lock->unlock();

        // GFS is really poor.  While the above "unlock" returns, GFS
        // doesn't clean up it's tables right away (which will potentially
        // cause the conditional locking for the "exclusive" lock to fail.
        // We will retry the conditional lock request several times on a
        // failure to get past this.  The performance hit is negligible
        // in the grand scheme of things and only will occur with GFS
        // filesystems or if another local process has the shared lock
        // on local filesystems.
        for ($retries = 0; $retries < 10; $retries++) {
            if ($lock->lock(LOCK_EX, true)) {
                // Exclusive lock is obtained!
                self::_stopReadLockProcessing($lockDirectory);
                return true;
            }

            // wait 1 microsecond
            usleep(1);
        }

        // Restore lock state
        $lock->lock(LOCK_SH);

        self::_stopReadLockProcessing($lockDirectory);
        return false;
    }

    public static function deEscalateReadLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->getFileObject(self::READ_LOCK_FILE);
        $lock->lock(LOCK_SH);
    }

    public static function obtainOptimizationLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->createFile(self::OPTIMIZATION_LOCK_FILE);
        if (!$lock->lock(LOCK_EX, true)) {
            return false;
        }
        return $lock;
    }

    public static function releaseOptimizationLock(Zend_Search_Lucene_Storage_Directory $lockDirectory)
    {
        $lock = $lockDirectory->getFileObject(self::OPTIMIZATION_LOCK_FILE);
        $lock->unlock();
    }

}
