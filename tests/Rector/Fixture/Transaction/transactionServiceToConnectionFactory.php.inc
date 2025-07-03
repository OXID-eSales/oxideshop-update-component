<?php
namespace OxidEsales\TestModule;

use OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface;

class TransactionTest
{
    public function __construct(
        private TransactionServiceInterface $transactionService
    ) {
    }
    
    public function executeInTransaction()
    {
        $this->transactionService->begin();
        
        try {
            $this->transactionService->commit();
        } catch (\Exception $e) {
            $this->transactionService->rollback();
            throw $e;
        }
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule;

use OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface;

class TransactionTest
{
    public function __construct(
        private \OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionFactoryInterface $transactionService
    ) {
    }
    
    public function executeInTransaction()
    {
        $connection = $this->transactionService->create();
        $connection->beginTransaction();
        
        try {
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
?>