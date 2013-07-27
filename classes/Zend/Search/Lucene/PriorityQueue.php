<?php


abstract class Zend_Search_Lucene_PriorityQueue
{

    private $_heap = array();

    public function put($element)
    {
        $nodeId   = count($this->_heap);
        $parentId = ($nodeId-1) >> 1;   // floor( ($nodeId-1)/2 )

        while ($nodeId != 0  &&  $this->_less($element, $this->_heap[$parentId])) {
            // Move parent node down
            $this->_heap[$nodeId] = $this->_heap[$parentId];

            // Move pointer to the next level of tree
            $nodeId   = $parentId;
            $parentId = ($nodeId-1) >> 1;   // floor( ($nodeId-1)/2 )
        }

        // Put new node into the tree
        $this->_heap[$nodeId] = $element;
    }

    public function top()
    {
        if (count($this->_heap) == 0) {
            return null;
        }

        return $this->_heap[0];
    }

    public function pop()
    {
        if (count($this->_heap) == 0) {
            return null;
        }

        $top = $this->_heap[0];
        $lastId = count($this->_heap) - 1;

        $nodeId  = 0;     // Start from a top
        $childId = 1;     // First child

        // Choose smaller child
        if ($lastId > 2  &&  $this->_less($this->_heap[2], $this->_heap[1])) {
            $childId = 2;
        }

        while ($childId < $lastId  &&
               $this->_less($this->_heap[$childId], $this->_heap[$lastId])
          ) {
            // Move child node up
            $this->_heap[$nodeId] = $this->_heap[$childId];

            $nodeId  = $childId;               // Go down
            $childId = ($nodeId << 1) + 1;     // First child

            // Choose smaller child
            if (($childId+1) < $lastId  &&
                $this->_less($this->_heap[$childId+1], $this->_heap[$childId])
               ) {
                $childId++;
            }
        }

        // Move last element to the new position
        $this->_heap[$nodeId] = $this->_heap[$lastId];
        unset($this->_heap[$lastId]);

        return $top;
    }

    public function clear()
    {
        $this->_heap = array();
    }

    abstract protected function _less($el1, $el2);
}

