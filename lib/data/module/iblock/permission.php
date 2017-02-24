<?namespace Intervolga\Migrato\Data\Module\Iblock;

use Bitrix\Main\Loader;
use Intervolga\Migrato\Data\BaseData;
use Intervolga\Migrato\Data\Module\Main\Group;
use Intervolga\Migrato\Data\Record;
use Intervolga\Migrato\Data\RecordId;
use Intervolga\Migrato\Data\Link;
use Intervolga\Migrato\Tool\XmlIdProvider\TableXmlIdProvider;

class Permission extends BaseData
{
	public function __construct()
	{
		Loader::includeModule("iblock");
		$this->xmlIdProvider = new TableXmlIdProvider($this);
	}

	public function getFilesSubdir()
	{
		return "/type/iblock/";
	}

	public function getList(array $filter = array())
	{
		$result = array();
		foreach ($this->getIblocks() as $iblockId)
		{
			$permissions = \CIBlock::GetGroupPermissions($iblockId);
			foreach ($permissions as $groupId => $permission)
			{
				$id = RecordId::createComplexId(array(
					"IBLOCK_ID" => intval($iblockId),
					"GROUP_ID" => intval($groupId),
				));
				$record = new Record($this);
				$record->setXmlId($this->getXmlIdProvider()->getXmlId($id));
				$record->setId($id);

				$record->setFields(array(
					"PERMISSION" => $permission,
				));

				$dependency = clone $this->getDependency("GROUP_ID");
				$dependency->setXmlId(
					Group::getInstance()->getXmlIdProvider()->getXmlId(RecordId::createNumericId($groupId))
				);
				$record->addDependency("GROUP_ID", $dependency);

				$dependency = clone $this->getDependency("IBLOCK_ID");
				$dependency->setXmlId(
					Iblock::getInstance()->getXmlIdProvider()->getXmlId(RecordId::createNumericId($iblockId))
				);
				$record->addDependency("IBLOCK_ID", $dependency);

				$result[] = $record;
			}
		}

		return $result;
	}

	/**
	 * @return array|int[]
	 */
	protected function getIblocks()
	{
		$result = array();
		$getList = \CIBlock::GetList();
		while ($iblock = $getList->fetch())
		{
			$result[] = $iblock["ID"];
		}

		return $result;
	}

	public function getDependencies()
	{
		return array(
			"GROUP_ID" => new Link(Group::getInstance()),
			"IBLOCK_ID" => new Link(Iblock::getInstance()),
		);
	}
}