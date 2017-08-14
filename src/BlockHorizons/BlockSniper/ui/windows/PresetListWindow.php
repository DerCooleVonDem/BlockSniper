<?php

declare(strict_types = 1);

namespace BlockHorizons\BlockSniper\ui\windows;

class PresetListWindow extends Window {

	public function process() {
		$presets = $this->getLoader()->getPresetManager()->getAllPresets();
		$this->data = [
			"type" => "form",
			"title" => "Preset List Menu",
			"content" => "Select a preset to view/edit.",
			"buttons" => []
		];
		foreach($presets as $key => $name) {
			$this->data["buttons"][$key] = [
				"text" => $name,
				"image" => [
					"type" => "url",
					"data" => "http://www.iconsdb.com/icons/preview/guacamole-green/list-xxl.png"
				]
			];
		}
	}
}