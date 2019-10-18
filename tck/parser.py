import sys,os,re

class Parser():
	
	def __init__(self):
		
		"""
		Given a design template string with markers,
		returns a dict of template snippets.
		
		Usage:
		parser = Parser()
		blocks = parser.blocks( str, fname )
		
		"""
		
		self._pattern_tag        = re.compile(r"<!--[\s*](\S+)[\s*]-->((?!<!--[\s*]\/\1[\s*]-->)[\s\S\n\r]*)<!--[\s*]\/\1[\s*]-->",re.MULTILINE|re.DOTALL|re.UNICODE)
		self._pattern_attribute  = re.compile(r"data-tck=\"&([^\"]+)\"\s\1=\"[^\"]+\"",re.MULTILINE|re.DOTALL|re.UNICODE)
		self._pattern_attrsimple = re.compile(r"data-tck=\"&([^\"]+)\"",re.MULTILINE|re.DOTALL|re.UNICODE)
		self._result             = {}
		self._path               = []

	def _clean(self,block):
		block = self._pattern_attribute.sub("#&\g<1>#",block)
		block = self._pattern_attrsimple.sub("#&\g<1>#",block)
		return block
	
	def _replacer(self, match):
		block = self._clean(match.group(2))
		block_name = match.group(1).replace('cut:','')
		self.blocks(block, block_name, False)
		return '<!-- paste:{} -->'.format(block_name)
	
	def blocks(self, design_string, block_name, reset=True):
		if reset:
			self._result = {}
			self._path   = []
		block_name = re.sub(r"\W",'',block_name)
		self._path.append(block_name)
		result = self._pattern_tag.sub(
			self._replacer,
			design_string,
		)
		self._result['.'.join(self._path)] = result
		self._path.pop()
		return self._result
