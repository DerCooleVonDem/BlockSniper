<?php

declare(strict_types=1);

namespace BlockHorizons\BlockSniper\ui\windows;

use BlockHorizons\BlockSniper\brush\Brush;
use BlockHorizons\BlockSniper\brush\types\BiomeType;
use BlockHorizons\BlockSniper\brush\types\PlantType;
use BlockHorizons\BlockSniper\brush\types\ReplaceType;
use BlockHorizons\BlockSniper\brush\types\TopLayerType;
use BlockHorizons\BlockSniper\brush\types\TreeType;
use BlockHorizons\BlockSniper\data\Translation;
use BlockHorizons\BlockSniper\exceptions\InvalidBlockException;
use BlockHorizons\BlockSniper\Loader;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BrushPropertiesWindow extends CustomWindow{

	public function __construct(Loader $loader, Brush $b){
		parent::__construct($this->t(Translation::UI_BRUSH_MENU_TITLE));

		if($b->mode === Brush::MODE_BRUSH && $b->getType()->usesSize() && !$b->getShape()->usesThreeLengths()){
			$this->addSlider($this->t(Translation::UI_BRUSH_MENU_SIZE), 0, $loader->config->maxSize, 1, $b->size, function(Player $player, float $value) use ($b){
				$b->size = (int) $value;
			});
		}
		if($b->mode === Brush::MODE_BRUSH && $b->getType()->usesSize() && !$b->getShape()->usesThreeLengths()){
			$this->addToggle($this->t(Translation::UI_BRUSH_MENU_DECREMENT), $b->decrementing, function(Player $player, bool $value) use ($b){
				$b->decrementing = $value;
				// Set the size the brush will reset to after reaching a size of 0.
				$b->resetSize = $b->size;
			});
		}
		if($b->getType()->canBeHollow()){
			$this->addToggle($this->t(Translation::UI_BRUSH_MENU_HOLLOW), $b->hollow, function(Player $player, bool $value) use ($b){
				$b->hollow = $value;
			});
		}
		if($b->mode === Brush::MODE_BRUSH && $b->getShape()->usesThreeLengths() && $b->getType()->usesSize()){
			$this->addSlider($this->t(Translation::UI_BRUSH_MENU_WIDTH), 0, $loader->config->maxSize, 1, $b->width, function(Player $player, float $value) use ($b){
				$b->width = (int) $value;
			});
			if($b->getType()::ID !== BiomeType::ID) {
				$this->addSlider($this->t(Translation::UI_BRUSH_MENU_HEIGHT), 0, $loader->config->maxSize, 1, $b->height, function(Player $player, float $value) use ($b){
					$b->height = (int) $value;
				});
			}
			$this->addSlider($this->t(Translation::UI_BRUSH_MENU_LENGTH), 0, $loader->config->maxSize, 1, $b->length, function(Player $player, float $value) use ($b){
				$b->length = (int) $value;
			});
		}
		if($b->getType()->usesBlocks()){
			$this->addInput($this->t(Translation::UI_BRUSH_MENU_BLOCKS), $b->blocks, "stone,cracked_stone_brick", function(Player $player, string $value) use ($b){
				try {
					$b->parseBlocks($value);
					$b->blocks = $value;
				} catch(InvalidBlockException $exception) {
					$player->sendMessage(TextFormat::RED . $exception->getMessage());
				}
			});
		}

		// Type specific properties.
		switch($b->getType()::ID){
			case TopLayerType::ID:
				$this->addSlider($this->t(Translation::UI_BRUSH_MENU_LAYER_WIDTH), 0, $loader->config->maxSize, 1, $b->layerWidth, function(Player $player, float $value) use ($b){
					$b->layerWidth = (int) $value;
				});
				break;
			case ReplaceType::ID:
				$this->addInput($this->t(Translation::UI_BRUSH_MENU_OBSOLETE), $b->obsolete, "stone,mossy_stone_brick,grass", function(Player $player, string $value) use ($b){
					try {
						$b->parseBlocks($value);
						$b->obsolete = $value;
					} catch(InvalidBlockException $exception) {
						$player->sendMessage(TextFormat::RED . $exception->getMessage());
					}
				});
				break;
			case BiomeType::ID:
				$this->addInput($this->t(Translation::UI_BRUSH_MENU_BIOME), (string) $b->biomeId, "plains", function(Player $player, string $value) use ($b){
					$b->biomeId = $b->parseBiomeId($value);
				});
				break;
			case TreeType::ID:
				$this->addTreeProperties($b, $loader);
				break;
			case PlantType::ID:
				$this->addInput($this->t(Translation::UI_BRUSH_MENU_SOIL), $b->soilBlocks, "grass", function(Player $player, string $value) use ($b){
					try {
						$b->parseBlocks($value);
						$b->soilBlocks = $value;
					} catch(InvalidBlockException $exception) {
						$player->sendMessage(TextFormat::RED . $exception->getMessage());
					}
				});
				break;
		}
	}

	private function addTreeProperties(Brush $b, Loader $loader){
		$this->addInput($this->t(Translation::UI_TREE_MENU_TRUNK_BLOCKS), $b->tree->trunkBlocks, "oak_wood,dark_oak_wood", function(Player $player, string $value) use ($b){
			try {
				$b->parseBlocks($value);
				$b->tree->trunkBlocks = $value;
			} catch(InvalidBlockException $exception) {
				$player->sendMessage(TextFormat::RED . $exception->getMessage());
			}
		});
		$this->addInput($this->t(Translation::UI_TREE_MENU_LEAVES_BLOCKS), $b->tree->leavesBlocks, "oak_leaves,spruce_leaves", function(Player $player, string $value) use ($b){
			try {
				$b->parseBlocks($value);
				$b->tree->leavesBlocks = $value;
			} catch(InvalidBlockException $exception) {
				$player->sendMessage(TextFormat::RED . $exception->getMessage());
			}
		});
		$this->addSlider($this->t(Translation::UI_TREE_MENU_TRUNK_HEIGHT), 0, $loader->config->maxSize, 1, $b->tree->trunkHeight, function(Player $player, float $value) use ($b){
			$b->tree->trunkHeight = (int) $value;
		});
		$this->addSlider($this->t(Translation::UI_TREE_MENU_TRUNK_WIDTH), 0, (int) ($loader->config->maxSize / 3), 1, $b->tree->trunkWidth, function(Player $player, float $value) use ($b){
			$b->tree->trunkWidth = (int) $value;
		});
		$this->addSlider($this->t(Translation::UI_TREE_MENU_MAX_BRANCH_LENGTH), 0, $loader->config->maxSize, 1, $b->tree->maxBranchLength, function(Player $player, float $value) use ($b){
			$b->tree->maxBranchLength = (int) $value;
		});
		$this->addSlider($this->t(Translation::UI_TREE_MENU_LEAVES_CLUSTER_SIZE), 0, $loader->config->maxSize / 2, 1, $b->tree->leavesClusterSize, function(Player $player, float $value) use ($b){
			$b->tree->leavesClusterSize = (int) $value;
		});
	}
}