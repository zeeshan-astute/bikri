var Hashtable=(function(){var FUNCTION="function";var arrayRemoveAt=(typeof Array.prototype.splice==FUNCTION)?function(arr,idx){arr.splice(idx,1);}:function(arr,idx){var itemsAfterDeleted,i,len;if(idx===arr.length-1){arr.length=idx;}else{itemsAfterDeleted=arr.slice(idx+1);arr.length=idx;for(i=0,len=itemsAfterDeleted.length;i<len;++i){arr[idx+i]=itemsAfterDeleted[i];}}};function hashObject(obj){var hashCode;if(typeof obj=="string"){return obj;}else if(typeof obj.hashCode==FUNCTION){hashCode=obj.hashCode();return(typeof hashCode=="string")?hashCode:hashObject(hashCode);}else if(typeof obj.toString==FUNCTION){return obj.toString();}else{try{return String(obj);}catch(ex){return Object.prototype.toString.call(obj);}}}
function equals_fixedValueHasEquals(fixedValue,variableValue){return fixedValue.equals(variableValue);}
function equals_fixedValueNoEquals(fixedValue,variableValue){return(typeof variableValue.equals==FUNCTION)?variableValue.equals(fixedValue):(fixedValue===variableValue);}
function createKeyValCheck(kvStr){return function(kv){if(kv===null){throw new Error("null is not a valid "+kvStr);}else if(typeof kv=="undefined"){throw new Error(kvStr+" must not be undefined");}};}
var checkKey=createKeyValCheck("key"),checkValue=createKeyValCheck("value");function Bucket(hash,firstKey,firstValue,equalityFunction){this[0]=hash;this.entries=[];this.addEntry(firstKey,firstValue);if(equalityFunction!==null){this.getEqualityFunction=function(){return equalityFunction;};}}
var EXISTENCE=0,ENTRY=1,ENTRY_INDEX_AND_VALUE=2;function createBucketSearcher(mode){return function(key){var i=this.entries.length,entry,equals=this.getEqualityFunction(key);while(i--){entry=this.entries[i];if(equals(key,entry[0])){switch(mode){case EXISTENCE:return true;case ENTRY:return entry;case ENTRY_INDEX_AND_VALUE:return[i,entry[1]];}}}
return false;};}
function createBucketLister(entryProperty){return function(aggregatedArr){var startIndex=aggregatedArr.length;for(var i=0,len=this.entries.length;i<len;++i){aggregatedArr[startIndex+i]=this.entries[i][entryProperty];}};}
Bucket.prototype={getEqualityFunction:function(searchValue){return(typeof searchValue.equals==FUNCTION)?equals_fixedValueHasEquals:equals_fixedValueNoEquals;},getEntryForKey:createBucketSearcher(ENTRY),getEntryAndIndexForKey:createBucketSearcher(ENTRY_INDEX_AND_VALUE),removeEntryForKey:function(key){var result=this.getEntryAndIndexForKey(key);if(result){arrayRemoveAt(this.entries,result[0]);return result[1];}
return null;},addEntry:function(key,value){this.entries[this.entries.length]=[key,value];},keys:createBucketLister(0),values:createBucketLister(1),getEntries:function(entries){var startIndex=entries.length;for(var i=0,len=this.entries.length;i<len;++i){entries[startIndex+i]=this.entries[i].slice(0);}},containsKey:createBucketSearcher(EXISTENCE),containsValue:function(value){var i=this.entries.length;while(i--){if(value===this.entries[i][1]){return true;}}
return false;}};function searchBuckets(buckets,hash){var i=buckets.length,bucket;while(i--){bucket=buckets[i];if(hash===bucket[0]){return i;}}
return null;}
function getBucketForHash(bucketsByHash,hash){var bucket=bucketsByHash[hash];return(bucket&&(bucket instanceof Bucket))?bucket:null;}
function Hashtable(hashingFunctionParam,equalityFunctionParam){var that=this;var buckets=[];var bucketsByHash={};var hashingFunction=(typeof hashingFunctionParam==FUNCTION)?hashingFunctionParam:hashObject;var equalityFunction=(typeof equalityFunctionParam==FUNCTION)?equalityFunctionParam:null;this.put=function(key,value){checkKey(key);checkValue(value);var hash=hashingFunction(key),bucket,bucketEntry,oldValue=null;bucket=getBucketForHash(bucketsByHash,hash);if(bucket){bucketEntry=bucket.getEntryForKey(key);if(bucketEntry){oldValue=bucketEntry[1];bucketEntry[1]=value;}else{bucket.addEntry(key,value);}}else{bucket=new Bucket(hash,key,value,equalityFunction);buckets[buckets.length]=bucket;bucketsByHash[hash]=bucket;}
return oldValue;};this.get=function(key){checkKey(key);var hash=hashingFunction(key);var bucket=getBucketForHash(bucketsByHash,hash);if(bucket){var bucketEntry=bucket.getEntryForKey(key);if(bucketEntry){return bucketEntry[1];}}
return null;};this.containsKey=function(key){checkKey(key);var bucketKey=hashingFunction(key);var bucket=getBucketForHash(bucketsByHash,bucketKey);return bucket?bucket.containsKey(key):false;};this.containsValue=function(value){checkValue(value);var i=buckets.length;while(i--){if(buckets[i].containsValue(value)){return true;}}
return false;};this.clear=function(){buckets.length=0;bucketsByHash={};};this.isEmpty=function(){return!buckets.length;};var createBucketAggregator=function(bucketFuncName){return function(){var aggregated=[],i=buckets.length;while(i--){buckets[i][bucketFuncName](aggregated);}
return aggregated;};};this.keys=createBucketAggregator("keys");this.values=createBucketAggregator("values");this.entries=createBucketAggregator("getEntries");this.remove=function(key){checkKey(key);var hash=hashingFunction(key),bucketIndex,oldValue=null;var bucket=getBucketForHash(bucketsByHash,hash);if(bucket){oldValue=bucket.removeEntryForKey(key);if(oldValue!==null){if(!bucket.entries.length){bucketIndex=searchBuckets(buckets,hash);arrayRemoveAt(buckets,bucketIndex);delete bucketsByHash[hash];}}}
return oldValue;};this.size=function(){var total=0,i=buckets.length;while(i--){total+=buckets[i].entries.length;}
return total;};this.each=function(callback){var entries=that.entries(),i=entries.length,entry;while(i--){entry=entries[i];callback(entry[0],entry[1]);}};this.putAll=function(hashtable,conflictCallback){var entries=hashtable.entries();var entry,key,value,thisValue,i=entries.length;var hasConflictCallback=(typeof conflictCallback==FUNCTION);while(i--){entry=entries[i];key=entry[0];value=entry[1];if(hasConflictCallback&&(thisValue=that.get(key))){value=conflictCallback(key,thisValue,value);}
that.put(key,value);}};this.clone=function(){var clone=new Hashtable(hashingFunctionParam,equalityFunctionParam);clone.putAll(that);return clone;};}
return Hashtable;})();
